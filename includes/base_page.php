<?php
/**
 * OVACS Base Page Class
 * Common functionality for all OVACS pages
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/ui_components.php';

abstract class BasePage {
    protected $pageTitle;
    protected $managers = [];
    protected $allowedFilters = [];
    protected $filters = [];
    protected $messages = [];
    
    public function __construct($pageTitle, $requiredManagers = [], $allowedFilters = []) {
        $this->pageTitle = $pageTitle;
        $this->allowedFilters = $allowedFilters;
        $this->initializeManagers($requiredManagers);
        $this->processFiltersFromRequest();
        $this->processMessages();
    }
    
    /**
     * Initialize required managers
     */
    private function initializeManagers($requiredManagers) {
        $instances = initializePage($requiredManagers);
        $this->managers = $instances;
    }
    
    /**
     * Process filters from GET request
     */
    private function processFiltersFromRequest() {
        $this->filters = processFilters($this->allowedFilters);
    }
    
    /**
     * Process success/error messages from URL
     */
    private function processMessages() {
        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'success':
                    $this->addMessage('success', $this->getSuccessMessage());
                    break;
                case 'error':
                    $this->addMessage('error', $this->getErrorMessage());
                    break;
            }
        }
    }
    
    /**
     * Add message to display
     */
    public function addMessage($type, $message, $title = '') {
        $this->messages[] = [
            'type' => $type,
            'message' => $message,
            'title' => $title
        ];
    }
    
    /**
     * Get success message (override in child classes)
     */
    protected function getSuccessMessage() {
        return 'Operation completed successfully.';
    }
    
    /**
     * Get error message (override in child classes)
     */
    protected function getErrorMessage() {
        return 'An error occurred while processing your request.';
    }
    
    /**
     * Render page header
     */
    public function renderHeader($additionalCss = '') {
        renderPageHeader($this->pageTitle, $additionalCss);
        include 'header.php';
    }
    
    /**
     * Render hero section
     */
    public function renderHero($subtitle = '', $extraContent = '') {
        echo '<section class="hero">
            <div class="container">';
        
        // Render messages
        foreach ($this->messages as $message) {
            renderAlert($message['type'], $message['message'], $message['title']);
        }
        
        echo '<h1 class="hero-title">' . safeHtml($this->pageTitle) . '</h1>';
        
        if ($subtitle) {
            echo '<p class="hero-subtitle">' . safeHtml($subtitle) . '</p>';
        }
        
        echo $extraContent;
        echo '</div>
        </section>';
    }
    
    /**
     * Render footer
     */
    public function renderFooter() {
        echo '<script src="js/common.js"></script>';
        include 'footer.php';
        echo '</body></html>';
    }
    
    /**
     * Get manager instance
     */
    protected function getManager($name) {
        $managerKey = $name . 'Manager';
        return $this->managers[$managerKey] ?? null;
    }
    
    /**
     * Redirect with preserved filters and message
     */
    protected function redirectWithMessage($page, $messageType = 'success', $additionalParams = []) {
        $params = array_merge($this->filters, ['msg' => $messageType], $additionalParams);
        $redirectUrl = buildRedirectUrl($page, $params);
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Process form submission (override in child classes)
     */
    abstract protected function processFormSubmission();
    
    /**
     * Render page content (override in child classes)
     */
    abstract protected function renderContent();
    
    /**
     * Main render method
     */
    public function render() {
        // Process any form submissions first
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processFormSubmission();
        }
        
        // Render the page
        $this->renderHeader();
        $this->renderContent();
        $this->renderFooter();
    }
}

/**
 * Table-based page class for pages with data tables
 */
abstract class TableBasePage extends BasePage {
    protected $tableHeaders = [];
    protected $tableData = [];
    
    /**
     * Get table headers (override in child classes)
     */
    abstract protected function getTableHeaders();
    
    /**
     * Get table data (override in child classes) 
     */
    abstract protected function getTableData();
    
    /**
     * Render the data table
     */
    protected function renderDataTable($tableId = 'dataTable') {
        $headers = $this->getTableHeaders();
        $data = $this->getTableData();
        
        renderDataTable($headers, $data, $tableId);
    }
}

/**
 * CRUD page class for pages with Create, Read, Update, Delete operations
 */
abstract class CRUDPage extends TableBasePage {
    protected $entityName;
    
    public function __construct($pageTitle, $entityName, $requiredManagers = [], $allowedFilters = []) {
        $this->entityName = $entityName;
        parent::__construct($pageTitle, $requiredManagers, $allowedFilters);
    }
    
    /**
     * Process CRUD form submissions
     */
    protected function processFormSubmission() {
        if (!isset($_POST['action'])) return;
        
        switch ($_POST['action']) {
            case 'add':
                $this->handleAdd();
                break;
            case 'edit':
                $this->handleEdit();
                break;
            case 'delete':
                $this->handleDelete();
                break;
            default:
                $this->handleCustomAction($_POST['action']);
        }
    }
    
    /**
     * Handle add operation (override in child classes)
     */
    abstract protected function handleAdd();
    
    /**
     * Handle edit operation (override in child classes)
     */
    abstract protected function handleEdit();
    
    /**
     * Handle delete operation (override in child classes)
     */
    abstract protected function handleDelete();
    
    /**
     * Handle custom actions (override in child classes if needed)
     */
    protected function handleCustomAction($action) {
        // Default: do nothing
    }
    
    /**
     * Render add button
     */
    protected function renderAddButton($modalId = null) {
        $onclick = $modalId ? "OVACSModal.show('{$modalId}')" : '';
        echo '<div style="padding: 2rem 0;">
            <div class="container">
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <button onclick="' . $onclick . '" style="
                        background: #2563eb; color: white; border: none;
                        padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 500; cursor: pointer;
                    ">Add New ' . safeHtml($this->entityName) . '</button>
                </div>
            </div>
        </div>';
    }
}