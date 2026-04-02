<?php
/**
 * OVACS UI Components
 * Reusable HTML components for consistent interface
 */

/**
 * Render alert message
 */
function renderAlert($type, $message, $title = '') {
    $styles = [
        'success' => ['bg' => '#d1e7dd', 'color' => '#0a3622', 'border' => '#badbcc', 'icon' => '✅'],
        'error' => ['bg' => '#f8d7da', 'color' => '#58151c', 'border' => '#f1aeb5', 'icon' => '⚠️'],
        'warning' => ['bg' => '#fff3cd', 'color' => '#6c4100', 'border' => '#ffe69c', 'icon' => '⚠️'],
        'info' => ['bg' => '#cff4fc', 'color' => '#055160', 'border' => '#9eeaf9', 'icon' => '🔍']
    ];
    
    $style = $styles[$type] ?? $styles['info'];
    
    echo '<div class="alert alert-' . $type . '" style="margin-top: 1rem; background: ' . $style['bg'] . '; color: ' . $style['color'] . '; padding: 10px; border-radius: 5px; border: 1px solid ' . $style['border'] . ';">';
    echo $style['icon'] . ' ';
    if ($title) echo '<strong>' . htmlspecialchars($title) . '</strong> ';
    echo htmlspecialchars($message);
    echo '</div>';
}

/**
 * Render page header with common meta tags and CSS
 */
function renderPageHeader($title, $additionalCss = '') {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - ' . htmlspecialchars($title) . '</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">';
    if ($additionalCss) {
        echo '<style>' . $additionalCss . '</style>';
    }
    echo '</head>
<body>';
}

/**
 * Render hero section with consistent styling
 */
function renderHeroSection($title, $subtitle = '', $extraContent = '') {
    echo '<section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) {
        echo '<p class="hero-subtitle">' . htmlspecialchars($subtitle) . '</p>';
    }
    echo $extraContent;
    echo '</div>
    </section>';
}

/**
 * Render vehicle status cards with dynamic data
 */
function renderVehicleStatusCards($statusData, $totalVehicles = null) {
    $output = '';
    
    foreach ($statusData as $status) {
        $darkColor = adjustBrightness($status['color_code'], -20);
        $output .= '<div class="stat-card" style="background: linear-gradient(135deg, ' . $status['color_code'] . ', ' . $darkColor . '); color: white; text-align: center;">';
        $output .= '<h3>' . $status['count'] . '</h3>';
        $output .= '<p>' . htmlspecialchars($status['status_name']) . '</p>';
        $output .= '</div>';
    }
    
    // Add total vehicles card if provided
    if ($totalVehicles !== null) {
        $output .= '<div class="stat-card" style="background: linear-gradient(135deg, #4a5568, #2d3748); color: white; border: 1px solid #e2e8f0; text-align: center;">';
        $output .= '<h3>' . $totalVehicles . '</h3>';
        $output .= '<p>Total Vehicles</p>';
        $output .= '</div>';
    }
    
    return $output;
}

/**
 * Render a 4-column responsive grid
 */
function render4ColumnGrid($contentCallback) {
    $output = '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 2rem 0;';
    $output .= ' /* Mobile */ @media (max-width: 480px) { grid-template-columns: 1fr; }';
    $output .= ' /* Tablet */ @media (max-width: 768px) { grid-template-columns: repeat(2, 1fr); }">';
    
    // Execute the callback to get the content
    ob_start();
    $contentCallback();
    $content = ob_get_clean();
    
    $output .= $content;
    $output .= '</div>';
    
    return $output;
}

/**
 * Render hero section with consistent styling
 */
function renderHeroSectionComplete($title, $subtitle = '', $extraContent = '') {
    echo '<section class="hero">
        <div class="container">
            <h1 class="hero-title">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) {
        echo '<p class="hero-subtitle">' . htmlspecialchars($subtitle) . '</p>';
    }
    echo $extraContent;
    echo '</div>
    </section>';
}

/**
 * Render active filters display
 */
function renderActiveFilters($filters, $filterLabels = []) {
    if (empty($filters)) return;
    
    echo '<div class="alert alert-info" style="margin-top: 1rem; background: #cff4fc; color: #055160; padding: 10px; border-radius: 5px; border: 1px solid #9eeaf9;">
        🔍 <strong>Active Filters:</strong> ';
    
    $activeFilters = [];
    foreach ($filters as $key => $value) {
        $label = $filterLabels[$key] ?? ucfirst($key);
        $activeFilters[] = $label . ": " . htmlspecialchars($value);
    }
    echo implode(', ', $activeFilters);
    echo '</div>';
}

/**
 * Render status badge
 */
function renderStatusBadge($statusName, $colorCode = '#6b7280') {
    echo '<span style="background: ' . htmlspecialchars($colorCode) . '; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">';
    echo htmlspecialchars($statusName);
    echo '</span>';
}

/**
 * Render vehicle type badge
 */
function renderVehicleTypeBadge($typeCode, $color = '#2563eb') {
    echo '<span style="background: ' . htmlspecialchars($color) . '; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">';
    echo htmlspecialchars($typeCode);
    echo '</span>';
}

/**
 * Render action button
 */
function renderActionButton($text, $onclick = '', $class = 'btn-primary', $style = '', $type = 'button') {
    $onclickAttr = $onclick ? 'onclick="' . htmlspecialchars($onclick) . '"' : '';
    $styleAttr = $style ? 'style="' . $style . '"' : '';
    
    echo '<button type="' . $type . '" class="' . $class . '" ' . $onclickAttr . ' ' . $styleAttr . '>';
    echo $text;
    echo '</button>';
}

/**
 * Render modal template
 */
function renderModal($modalId, $title, $formContent, $submitButtonText = 'Save', $submitButtonColor = '#10b981') {
    echo '<div id="' . htmlspecialchars($modalId) . '" style="
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); display: none; z-index: 1000;
        backdrop-filter: blur(5px);
    ">
        <div style="
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: white; border-radius: 8px; padding: 2rem; width: 90%; max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        ">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; color: #1f2937;">' . htmlspecialchars($title) . '</h3>
                <span onclick="hide' . ucfirst($modalId) . '()" style="
                    cursor: pointer; font-size: 1.5rem; color: #6b7280; 
                    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
                    border-radius: 50%; background: #f3f4f6;
                ">&times;</span>
            </div>
            ' . $formContent . '
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" form="' . $modalId . 'Form" style="
                    background: ' . $submitButtonColor . '; color: white; border: none;
                    padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 500; cursor: pointer;
                ">' . htmlspecialchars($submitButtonText) . '</button>
                <button type="button" onclick="hide' . ucfirst($modalId) . '()" style="
                    background: #6b7280; color: white; border: none;
                    padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 500; cursor: pointer;
                ">Cancel</button>
            </div>
        </div>
    </div>';
}

/**
 * Render data table
 */
function renderDataTable($headers, $rows, $tableId = 'dataTable') {
    echo '<div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <table id="' . htmlspecialchars($tableId) . '" style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa;">
                <tr>';
    
    foreach ($headers as $header) {
        echo '<th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem; border-bottom: 1px solid #dee2e6;">';
        echo htmlspecialchars($header);
        echo '</th>';
    }
    
    echo '</tr>
            </thead>
            <tbody>';
    
    foreach ($rows as $row) {
        echo '<tr style="border-bottom: 1px solid #f1f3f4;">';
        foreach ($row as $cell) {
            echo '<td style="padding: 0.75rem 1rem; font-size: 0.875rem; color: #374151;">';
            echo $cell; // Allow HTML content in cells
            echo '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody>
        </table>
    </div>';
}