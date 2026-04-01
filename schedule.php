<?php
/**
 * OVACS 24-Hour Schedule Planning
 * Online Vehicle Availability Control System
 */

require_once 'includes/common.php';
require_once 'includes/ui_components.php';

// Include database configuration
include 'includes/database.php';

// Get database connection
$pdo = DatabaseConfig::getConnection();
$stationManager = new StationManager();

// Handle station selection
$selected_station_id = $_GET['station_id'] ?? null;
$selected_station = null;

// Get all stations for dropdown
$stations_query = "SELECT * FROM stations ORDER BY station_code";
$stations_result = $pdo->query($stations_query);
$stations = $stations_result->fetchAll();

// Get selected station details
if ($selected_station_id) {
    $station_query = "SELECT * FROM stations WHERE id = ?";
    $stmt = $pdo->prepare($station_query);
    $stmt->execute([$selected_station_id]);
    $selected_station = $stmt->fetch();
}

// Get current time and next 24 hours (using Europe/London timezone for BST/GMT handling)
$current_time = new DateTime('now', new DateTimeZone('Europe/London'));
$start_time_offset = clone $current_time;
$start_time_offset->sub(new DateInterval('PT4H')); // Start 4 hours before current time
$end_time = clone $start_time_offset;
$end_time->add(new DateInterval('P1D')); // Add 1 day

// Function to get shift requirements for a station
function getShiftRequirements($pdo, $station_id) {
    $query = "
        SELECT sr.*, vt.type_code, vt.type_name, sp.pattern_name, 
               sp.start_time, sp.end_time, sp.duration_hours
        FROM station_requirements sr
        JOIN vehicle_types vt ON sr.vehicle_type_id = vt.id
        JOIN shift_patterns sp ON sr.shift_pattern_id = sp.id
        WHERE sr.station_id = ?
        ORDER BY sp.start_time, vt.type_code
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$station_id]);
    return $stmt->fetchAll();
}

// Function to generate 24-hour timeline
function generateTimeSlots() {
    $slots = [];
    $time = new DateTime('00:00');
    
    for ($i = 0; $i < 24; $i++) {
        $slots[] = $time->format('H:i');
        $time->add(new DateInterval('PT1H'));
    }
    
    return $slots;
}

// Get system-wide requirements for current hour (all stations)
$system_wide_dca = 0;
$system_wide_rrv = 0;

$system_query = "
    SELECT sr.required_count, vt.type_code, sp.start_time, sp.duration_hours
    FROM station_requirements sr
    JOIN vehicle_types vt ON sr.vehicle_type_id = vt.id
    JOIN shift_patterns sp ON sr.shift_pattern_id = sp.id
";
$system_result = $pdo->query($system_query);
$system_requirements = $system_result->fetchAll();

foreach ($system_requirements as $req) {
    // Create DateTime objects for precise time comparison
    $current_time_today = clone $current_time;
    $current_time_today->setTime((int)$current_time->format('H'), (int)$current_time->format('i'), 0);
    
    $pattern_start_time = DateTime::createFromFormat('H:i:s', $req['start_time'], new DateTimeZone('Europe/London'));
    $shift_start = clone $current_time_today;
    $shift_start->setTime((int)$pattern_start_time->format('H'), (int)$pattern_start_time->format('i'), 0);
    
    $shift_end = clone $shift_start;
    $shift_end->add(new DateInterval('PT' . $req['duration_hours'] . 'H'));
    
    // Handle overnight shifts that cross midnight
    if ($shift_end->format('Y-m-d') > $shift_start->format('Y-m-d')) {
        // Overnight shift - check if current time is after start OR before end (next day)
        $end_next_day = clone $shift_end;
        $start_next_day = clone $current_time_today;
        $start_next_day->add(new DateInterval('P1D'));
        
        $is_active = ($current_time_today >= $shift_start) || 
                     ($current_time_today < $shift_end->setDate(
                         (int)$current_time_today->format('Y'),
                         (int)$current_time_today->format('m'),
                         (int)$current_time_today->format('d')
                     ));
    } else {
        // Regular shift - current time must be between start and end
        $is_active = ($current_time_today >= $shift_start && $current_time_today < $shift_end);
    }
    
    if ($is_active) {
        if ($req['type_code'] == 'DCA') {
            $system_wide_dca += $req['required_count'];
        } elseif ($req['type_code'] == 'RRV') {
            $system_wide_rrv += $req['required_count'];
        }
    }
}

// Get requirements for selected station
$requirements = [];
$timeline_data = [];
if ($selected_station_id) {
    $requirements = getShiftRequirements($pdo, $selected_station_id);
    
    // Initialize timeline data array for 24 hours starting from start_time_offset
    for ($i = 0; $i < 24; $i++) {
        $display_hour = ((int)$start_time_offset->format('H') + $i) % 24;
        $hour_key = sprintf('%02d:00', $display_hour);
        $timeline_data[$hour_key] = array();
    }
    
    // Process requirements into timeline format with shift indicators
    foreach ($requirements as $req) {
        $pattern_start_time = DateTime::createFromFormat('H:i:s', $req['start_time'], new DateTimeZone('Europe/London'));
        $duration = $req['duration_hours'];
        $pattern_start_hour = (int)$pattern_start_time->format('H');
        $pattern_end_hour = ($pattern_start_hour + $duration) % 24;
        
        // Loop through timeline window (24 hours from start_time_offset)
        for ($i = 0; $i < 24; $i++) {
            $hour = ((int)$start_time_offset->format('H') + $i) % 24;
            $current_hour = sprintf('%02d:00', $hour);
            
            // Simple hour-based comparison for shift detection
            $is_hour_in_shift = false;
            
            // Handle overnight shifts (e.g., 19:00 to 07:00)
            if ($pattern_end_hour <= $pattern_start_hour) {
                // Overnight shift
                if ($hour >= $pattern_start_hour || $hour < $pattern_end_hour) {
                    $is_hour_in_shift = true;
                }
            } else {
                // Regular shift (same day, e.g., 07:00 to 19:00)  
                if ($hour >= $pattern_start_hour && $hour < $pattern_end_hour) {
                    $is_hour_in_shift = true;
                }
            }
            
            if ($is_hour_in_shift) {
                // Determine shift position (start, middle, end)
                $position = 'middle';
                if ($hour == $pattern_start_hour) $position = 'start';
                if ($hour == ($pattern_end_hour - 1 + 24) % 24) $position = 'end';
                
                // Create unique key for vehicle type + shift combination
                $combination_key = $req['type_code'] . '|' . $req['pattern_name'];
                
                // Initialize the hour/combination if it doesn't exist
                if (!isset($timeline_data[$current_hour][$combination_key])) {
                    $timeline_data[$current_hour][$combination_key] = [
                        'count' => $req['required_count'],
                        'type_code' => $req['type_code'],
                        'type_name' => $req['type_name'],
                        'pattern_name' => $req['pattern_name'],
                        'position' => $position,
                        'start_time' => $req['start_time'],
                        'end_time' => $req['end_time']
                    ];
                }
            }
        }
    }
}

// Calculate vehicle requirements by type for the current hour (selected station)
$current_hour_totals = [];
$current_hour_time = sprintf('%02d:00', (int)$current_time->format('H'));
if (isset($timeline_data[$current_hour_time])) {
    foreach ($timeline_data[$current_hour_time] as $combination_key => $combination_data) {
        $type_code = $combination_data['type_code'];
        if (!isset($current_hour_totals[$type_code])) {
            $current_hour_totals[$type_code] = ['count' => 0, 'type_name' => $combination_data['type_name']];
        }
        $current_hour_totals[$type_code]['count'] += $combination_data['count'];
    }
}

// Calculate system-wide vehicle requirements for all stations
$system_wide_totals = [];
$total_vehicles = 0;

// Get all requirements across all stations for current time
$systemwide_sql = "SELECT sr.required_count, vt.type_code, vt.type_name, sp.pattern_name, sp.start_time, sp.end_time, sp.duration_hours
    FROM station_requirements sr
    JOIN vehicle_types vt ON sr.vehicle_type_id = vt.id
    JOIN shift_patterns sp ON sr.shift_pattern_id = sp.id";

$systemwide_stmt = $pdo->prepare($systemwide_sql);
$systemwide_stmt->execute();
$systemwide_requirements = $systemwide_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($systemwide_requirements as $req) {
    // Check if this shift pattern is currently active using precise time calculations
    $pattern_start = DateTime::createFromFormat('H:i:s', $req['start_time'], new DateTimeZone('Europe/London'));
    $pattern_duration_hours = $req['duration_hours'];
    $pattern_end = clone $pattern_start;
    $pattern_end->add(new DateInterval('PT' . $pattern_duration_hours . 'H'));
    
    $is_active = false;
    
    if ($pattern_end->format('H:i') <= $pattern_start->format('H:i')) {
        // Overnight shift
        if ($current_time >= $pattern_start || $current_time < $pattern_end) {
            $is_active = true;
        }
    } else {
        // Regular shift
        if ($current_time >= $pattern_start && $current_time < $pattern_end) {
            $is_active = true;
        }
    }
    
    if ($is_active) {
        if (!isset($system_wide_totals[$req['type_code']])) {
            $system_wide_totals[$req['type_code']] = [
                'count' => 0,
                'type_name' => $req['type_name']
            ];
        }
        $system_wide_totals[$req['type_code']]['count'] += $req['required_count'];
        $total_vehicles += $req['required_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - 24-Hour Schedule Planning</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .timeline-container {
            overflow-x: auto;
            margin: 2rem 0;
        }
        
        .timeline-table {
            min-width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .timeline-table th {
            background: #f8f9fa;
            padding: 1rem 0.5rem;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #e0e6ed;
            min-width: 60px;
        }
        
        .timeline-table td {
            padding: 1rem 0.5rem;
            text-align: center;
            border: 1px solid #e0e6ed;
            vertical-align: top;
        }
        
        .current-hour {
            background: #fff3cd !important;
            border-color: #ffc107 !important;
        }
        
        .vehicle-requirement {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 6px;
            padding: 0.5rem;
            margin: 0.2rem 0;
            font-size: 0.8rem;
            position: relative;
        }
        
        .vehicle-requirement.dca {
            background: #e8f5e8;
            border-color: #4caf50;
            color: #2e7d32;
        }
        
        .vehicle-requirement.rrv {
            background: #fff3e0;
            border-color: #ff9800;
            color: #f57c00;
        }
        
        .vehicle-requirement.shift-start {
            border-left: 4px solid #dc2626;
            background: linear-gradient(to right, #fee2e2 0%, inherit 20%);
        }
        
        .vehicle-requirement.shift-end {
            border-right: 4px solid #dc2626;
            background: linear-gradient(to left, #fee2e2 0%, inherit 20%);
        }
        
        .shift-time-marker {
            position: absolute;
            top: -8px;
            right: 2px;
            background: #dc2626;
            color: white;
            font-size: 0.6rem;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .shift-time-marker.start {
            background: #059669;
        }
        
        .shift-time-marker.end {
            background: #dc2626;
        }
        
        .requirement-count {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .shift-indicator {
            font-size: 0.75rem;
            color: #374151;
            margin-top: 0.3rem;
            font-weight: 500;
            background: rgba(255,255,255,0.8);
            padding: 2px 4px;
            border-radius: 3px;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .shift-name-header {
            font-size: 0.65rem;
            color: #6b7280;
            margin-top: 3px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .station-selector {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            display: block;
        }
        
        .summary-label {
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        .hour-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            min-width: 40px;
        }
        
        .next-change-column {
            background-color: #fee2e2 !important;
            box-shadow: inset 0 0 0 2px #fca5a5;
        }
        
        .hero-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .hero-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .timeline-table th,
            .timeline-table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }
            
            .vehicle-requirement {
                padding: 0.3rem;
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 480px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .hero-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">24-Hour Schedule Planning</h1>
            <p class="hero-subtitle">View vehicle and shift requirements for the next 24 hours</p>
            
            <!-- System-wide Vehicle Requirements Cards -->
            <div class="hero-cards">
                <?php 
                // Define colors for different vehicle types
                $vehicle_colors = [
                    'DCA' => '#2563eb',  // Blue
                    'RRV' => '#ea580c',  // Orange
                    'ORV' => '#7c3aed'   // Purple
                ];
                
                // Display cards for each vehicle type
                foreach ($system_wide_totals as $type_code => $data): 
                    $color = isset($vehicle_colors[$type_code]) ? $vehicle_colors[$type_code] : '#6b7280';
                ?>
                <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                    <span style="font-size: 2.5rem; font-weight: 700; color: <?php echo $color; ?>; display: block;"><?php echo $data['count']; ?></span>
                    <div style="color: #6b7280; margin-top: 0.5rem; font-weight: 600;"><?php echo $type_code; ?> Required Now</div>
                    <div style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">All Stations</div>
                </div>
                <?php endforeach; ?>
                
                <!-- Total Vehicles Card -->
                <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                    <span style="font-size: 2.5rem; font-weight: 700; color: #059669; display: block;"><?php echo $total_vehicles; ?></span>
                    <div style="color: #6b7280; margin-top: 0.5rem; font-weight: 600;">Total Vehicles</div>
                    <div style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">System-wide</div>
                </div>
            </div>
        </div>
    </section>

    <main class="container">
        <!-- Station Selector -->
        <div class="station-selector">
            <form method="GET" action="">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <label for="station_id" style="font-weight: 600; color: #374151;">Select Station:</label>
                    <select name="station_id" id="station_id" onchange="this.form.submit()" 
                            style="padding: 0.5rem; border-radius: 6px; border: 1px solid #d1d5db; flex: 1; max-width: 300px;">
                        <option value="">-- Choose a Station --</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['id']; ?>" 
                                    <?php echo ($selected_station_id == $station['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($station['station_code'] . ' - ' . $station['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_station): ?>
            <!-- Station Information -->
            <div class="summary-cards">
                <div class="summary-card">
                    <span class="summary-number"><?php echo htmlspecialchars($selected_station['station_code']); ?></span>
                    <div class="summary-label"><?php echo htmlspecialchars($selected_station['name']); ?></div>
                </div>
                <div class="summary-card">
                    <div style="display: flex; gap: 1rem; justify-content: center; align-items: center; flex-wrap: wrap;">
                        <?php 
                        $vehicle_colors = [
                            'DCA' => '#2563eb',
                            'RRV' => '#ea580c', 
                            'ORV' => '#7c3aed'
                        ];
                        // Sort vehicle types for consistent ordering
                        $sorted_current_totals = $current_hour_totals;
                        ksort($sorted_current_totals);
                        foreach ($sorted_current_totals as $type_code => $data): 
                            $color = $vehicle_colors[$type_code] ?? '#6b7280';
                        ?>
                            <div style="text-align: center;">
                                <span class="summary-number" style="font-size: 1.8rem; color: <?php echo $color; ?>;"><?php echo $data['count']; ?></span>
                                <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600;"><?php echo htmlspecialchars($type_code); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-label">Vehicles Required Now</div>
                </div>
                <div class="summary-card">
                    <span class="summary-number"><?php echo $current_time->format('H:i'); ?></span>
                    <div class="summary-label">Current Time</div>
                </div>
                <div class="summary-card">
                    <?php 
                    // Find the next change in requirements
                    $current_hour_key = $current_time->format('H:00');
                    $next_change_time = null;
                    $next_change_totals = [];
                    
                    // Sort timeline hours to check in order
                    $timeline_hours = array_keys($timeline_data);
                    sort($timeline_hours);

                    // Find current requirements for comparison (selected station only)
                    $current_requirements = [];
                    foreach ($current_hour_totals as $type_code => $data) {
                        $current_requirements[$type_code] = $data['count'];
                    }
                    
                    // Also initialize any vehicle types that might appear in future hours but not current
                    foreach ($system_wide_totals as $type_code => $data) {
                        if (!isset($current_requirements[$type_code])) {
                            $current_requirements[$type_code] = 0;
                        }
                    }

                    // Check each hour after current hour for a change
                    foreach ($timeline_hours as $hour) {
                        if ($hour <= $current_hour_key) continue; // Skip past/current hours
                        
                        $hour_totals = [];
                        
                        // Initialize all vehicle type counts to 0 (same types as current requirements)
                        foreach ($current_requirements as $type_code => $count) {
                            $hour_totals[$type_code] = 0;
                        }
                        
                        if (isset($timeline_data[$hour])) {
                            foreach ($timeline_data[$hour] as $combo_key => $data) {
                                // Extract vehicle type from combo key (format: "TYPE|Pattern Name")
                                $combo_parts = explode('|', $combo_key, 2);
                                if (count($combo_parts) >= 2) {
                                    $vehicle_type = $combo_parts[0];
                                    if (isset($hour_totals[$vehicle_type])) {
                                        $hour_totals[$vehicle_type] += $data['count'];
                                    }
                                }
                            }
                        }
                        
                        // Check if requirements changed for any vehicle type
                        $requirements_changed = false;
                        foreach ($current_requirements as $type_code => $current_count) {
                            $future_count = isset($hour_totals[$type_code]) ? $hour_totals[$type_code] : 0;
                            if ($future_count != $current_count) {
                                $requirements_changed = true;
                                break;
                            }
                        }
                        
                        if ($requirements_changed) {
                            $next_change_time = DateTime::createFromFormat('H:i', $hour, new DateTimeZone('Europe/London'));
                            // Build proper structure for next_change_totals with type names
                            $next_change_totals = [];
                            foreach ($hour_totals as $type_code => $count) {
                                if ($count > 0) {
                                    // Find the type name from current_hour_totals or system_wide_totals
                                    $type_name = isset($current_hour_totals[$type_code]) 
                                        ? $current_hour_totals[$type_code]['type_name']
                                        : (isset($system_wide_totals[$type_code]) ? $system_wide_totals[$type_code]['type_name'] : $type_code);
                                    
                                    $next_change_totals[$type_code] = $count;
                                }
                            }
                            break;
                        }
                    }
                    
                    // If no change found in remaining hours, check first hours of next day
                    if (!$next_change_time) {
                        foreach ($timeline_hours as $hour) {
                            if ($hour > $current_hour_key) break; // Only check early hours
                            
                            $hour_totals = [];
                            
                            // Initialize all vehicle type counts to 0 (same types as current requirements)
                            foreach ($current_requirements as $type_code => $count) {
                                $hour_totals[$type_code] = 0;
                            }
                            
                            if (isset($timeline_data[$hour])) {
                                foreach ($timeline_data[$hour] as $combo_key => $data) {
                                    // Extract vehicle type from combo key
                                    $combo_parts = explode('|', $combo_key, 2);
                                    if (count($combo_parts) >= 2) {
                                        $vehicle_type = $combo_parts[0];
                                        if (isset($hour_totals[$vehicle_type])) {
                                            $hour_totals[$vehicle_type] += $data['count'];
                                        }
                                    }
                                }
                            }
                            
                            // Check if requirements changed for any vehicle type
                            $requirements_changed = false;
                            foreach ($current_requirements as $type_code => $current_count) {
                                $future_count = isset($hour_totals[$type_code]) ? $hour_totals[$type_code] : 0;
                                if ($future_count != $current_count) {
                                    $requirements_changed = true;
                                    break;
                                }
                            }
                            
                            if ($requirements_changed) {
                                $next_change_time = DateTime::createFromFormat('H:i', $hour, new DateTimeZone('Europe/London'));
                                // Build proper structure for next_change_totals with type names
                                $next_change_totals = [];
                                foreach ($hour_totals as $type_code => $count) {
                                    if ($count > 0) {
                                        // Find the type name from current_hour_totals or system_wide_totals
                                        $type_name = isset($current_hour_totals[$type_code]) 
                                            ? $current_hour_totals[$type_code]['type_name']
                                            : (isset($system_wide_totals[$type_code]) ? $system_wide_totals[$type_code]['type_name'] : $type_code);
                                        
                                        $next_change_totals[$type_code] = $count;
                                    }
                                }
                                break;
                            }
                        }
                    }
                    
                    // Calculate next change hour for timeline highlighting
                    $next_change_hour = null;
                    if ($next_change_time) {
                        $next_change_hour = (int) $next_change_time->format('H');
                    }
                    ?>
                    
                    <?php if ($next_change_time): ?>
                    <div style="display: flex; gap: 1rem; justify-content: center; align-items: center; flex-wrap: wrap;">
                        <?php 
                        // Define colors for different vehicle types
                        $vehicle_colors = [
                            'DCA' => '#2563eb',  // Blue
                            'RRV' => '#ea580c',  // Orange
                            'ORV' => '#7c3aed'   // Purple
                        ];
                        
                        // Sort vehicle types for consistent ordering
                        ksort($next_change_totals);
                        foreach ($next_change_totals as $type_code => $count): 
                            $color = isset($vehicle_colors[$type_code]) ? $vehicle_colors[$type_code] : '#6b7280';
                        ?>
                        <div style="text-align: center;">
                            <span class="summary-number" style="font-size: 1.8rem; color: <?php echo $color; ?>;"><?php echo $count; ?></span>
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600;"><?php echo $type_code; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-label">Next Change: <?php echo $next_change_time->format('H:i'); ?></div>
                    <?php else: ?>
                    <span class="summary-number">No Change</span>
                    <div class="summary-label">Today</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 24-Hour Timeline -->
            <div class="timeline-container">
                <table class="timeline-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; min-width: 120px;">Time</th>
                            <?php 
                            $current_hour = (int)$current_time->format('H');
                            $start_hour_offset = (int)$start_time_offset->format('H');
                            for ($i = 0; $i < 24; $i++): 
                                $hour = ($start_hour_offset + $i) % 24;
                                $hour_class = ($hour == $current_hour) ? 'current-hour' : '';
                                $next_change_class = ($hour == $next_change_hour) ? 'next-change-column' : '';
                                $header_classes = trim($hour_class . ' ' . $next_change_class);
                                
                                // Check if this hour has shift transitions
                                $has_start = false;
                                $has_end = false;
                                $active_shifts = [];
                                
                                // Get active shifts and transitions for this hour
                                $time_slot = sprintf('%02d:00', $hour);
                                if (isset($timeline_data[$time_slot])) {
                                    foreach ($timeline_data[$time_slot] as $combination_key => $data) {
                                        if ($data['position'] == 'start') $has_start = true;
                                        if ($data['position'] == 'end') $has_end = true;
                                        if (!in_array($data['pattern_name'], $active_shifts)) {
                                            $active_shifts[] = $data['pattern_name'];
                                        }
                                    }
                                }
                            ?>
                                <th class="hour-header <?php echo $header_classes; ?>">
                                    <div><?php echo sprintf('%02d:00', $hour); ?></div>
                                    <?php if ($has_start): ?>
                                        <div style="font-size: 0.6rem; color: #059669; margin-top: 2px;">▲ Start</div>
                                    <?php endif; ?>
                                    <?php if ($has_end): ?>
                                        <div style="font-size: 0.6rem; color: #dc2626; margin-top: 2px;">▼ End</div>
                                    <?php endif; ?>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Get unique vehicle type/shift combinations -->
                        <?php 
                        // Get all unique combinations from requirements
                        $combinations = [];
                        foreach ($requirements as $req) {
                            $key = $req['type_code'] . '|' . $req['pattern_name'];
                            if (!isset($combinations[$key])) {
                                $combinations[$key] = [
                                    'type_code' => $req['type_code'],
                                    'type_name' => $req['type_name'],
                                    'pattern_name' => $req['pattern_name'],
                                    'start_time' => $req['start_time'],
                                    'end_time' => $req['end_time']
                                ];
                            }
                        }
                        
                        // Sort combinations by type_code then pattern_name
                        uksort($combinations, function($a, $b) {
                            $a_parts = explode('|', $a);
                            $b_parts = explode('|', $b);
                            if ($a_parts[0] == $b_parts[0]) {
                                return strcmp($a_parts[1], $b_parts[1]);
                            }
                            return strcmp($a_parts[0], $b_parts[0]);
                        });
                        
                        foreach ($combinations as $combination_key => $combo): 
                            $bg_color = ($combo['type_code'] == 'DCA') ? '#f0f9ff' : '#fefce8';
                            $text_color = ($combo['type_code'] == 'DCA') ? '#1e40af' : '#a16207';
                        ?>
                        <tr>
                            <td style="text-align: left; font-weight: 600; background: <?php echo $bg_color; ?>;">
                                <div style="color: <?php echo $text_color; ?>;"><?php echo htmlspecialchars($combo['type_code']); ?></div>
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;"><?php echo htmlspecialchars($combo['pattern_name']); ?></div>
                                <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 1px;"><?php echo substr($combo['start_time'], 0, 5) . '-' . substr($combo['end_time'], 0, 5); ?></div>
                            </td>
                            <?php for ($i = 0; $i < 24; $i++): 
                                $hour = ($start_hour_offset + $i) % 24;
                                $time_slot = sprintf('%02d:00', $hour);
                                $hour_class = ($hour == $current_hour) ? 'current-hour' : '';
                                $next_change_class = ($hour == $next_change_hour) ? 'next-change-column' : '';
                                $cell_classes = trim($hour_class . ' ' . $next_change_class);
                                $combination_data = $timeline_data[$time_slot][$combination_key] ?? null;
                            ?>
                                <td class="<?php echo $cell_classes; ?>">
                                    <?php if ($combination_data): ?>
                                        <div class="vehicle-requirement <?php echo strtolower($combo['type_code']); ?> <?php echo 'shift-' . $combination_data['position']; ?>">
                                            <?php if ($combination_data['position'] == 'start'): ?>
                                                <div class="shift-time-marker start"><?php echo substr($combination_data['start_time'], 0, 5); ?></div>
                                            <?php elseif ($combination_data['position'] == 'end'): ?>
                                                <div class="shift-time-marker end"><?php echo substr($combination_data['end_time'], 0, 5); ?></div>
                                            <?php endif; ?>
                                            <div class="requirement-count"><?php echo $combination_data['count']; ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Shift Patterns Summary -->
            <?php if (!empty($requirements)): ?>
                <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: #374151;">Active Shift Patterns</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <?php 
                        $patterns = [];
                        foreach ($requirements as $req) {
                            $key = $req['pattern_name'];
                            if (!isset($patterns[$key])) {
                                $patterns[$key] = [
                                    'pattern' => $req,
                                    'vehicles' => []
                                ];
                            }
                            $patterns[$key]['vehicles'][] = $req['required_count'] . 'x ' . $req['type_code'];
                        }
                        
                        foreach ($patterns as $pattern_data): 
                            $pattern = $pattern_data['pattern'];
                        ?>
                            <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem;">
                                <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($pattern['pattern_name']); ?>
                                </div>
                                <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <?php echo $pattern['start_time']; ?> - <?php echo $pattern['end_time']; ?>
                                    (<?php echo $pattern['duration_hours']; ?> hours)
                                </div>
                                <div style="color: #374151; font-size: 0.9rem;">
                                    Vehicles: <?php echo implode(', ', $pattern_data['vehicles']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Station Selected -->
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <h3 style="margin-bottom: 1rem;">Select a Station</h3>
                <p>Choose a station from the dropdown above to view its 24-hour vehicle and shift requirements schedule.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Auto-refresh page every 5 minutes to keep timeline current
        setTimeout(function() {
            if (<?php echo $selected_station_id ? 'true' : 'false'; ?>) {
                location.reload();
            }
        }, 300000); // 5 minutes

        // Highlight current hour
        document.addEventListener('DOMContentLoaded', function() {
            const currentHour = new Date().getHours();
            const currentCells = document.querySelectorAll('.current-hour');
            currentCells.forEach(cell => {
                cell.style.background = '#fff3cd';
                cell.style.borderColor = '#ffc107';
            });
        });
    </script>
</body>
</html>