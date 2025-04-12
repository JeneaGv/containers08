<?php

class Page {
    private $template;

    /**
     * Constructor for the Page class
     * @param string $template Path to the template file
     */
    public function __construct($template) {
        if (!file_exists($template)) {
            die("Template file not found: $template");
        }
        $this->template = $template;
    }

    /**
     * Render the page with provided data
     * @param array $data Associative array of data to replace in template
     * @return string Rendered page content
     */
    public function Render($data) {
        $content = file_get_contents($this->template);
        
        if (!$content) {
            die("Failed to read template file");
        }
        
        // Replace template variables with actual data
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        return $content;
    }
}