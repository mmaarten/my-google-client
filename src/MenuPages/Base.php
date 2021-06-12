<?php

namespace My\GoogleClient\MenuPages;

abstract class Base
{
    protected $page_hook = null;
    protected $parent_slug = '';
    protected $page_title = '';
    protected $menu_title = '';
    protected $capability = '';
    protected $menu_slug = '';
    protected $position = '';

    protected $sections = [];
    protected $fields   = [];

    public function __construct($id, $args = [])
    {
        $args = wp_parse_args($args, [
            'parent_slug' => 'options-general.php',
            'page_title'  => __('Untitled', 'my-google-client'),
            'menu_title'  => __('Untitled', 'my-google-client'),
            'capability'  => 'manage_options',
            'menu_slug'   => "my-google-client-{$id}-settings",
            'position'    => null,
            'option_name' => "my_google_client_{$id}_options",
        ]);

        $this->id = $id;
        $this->parent_slug = $args['parent_slug'];
        $this->page_title  = $args['page_title'];
        $this->menu_title  = $args['menu_title'];
        $this->capability  = $args['capability'];
        $this->menu_slug   = $args['menu_slug'];
        $this->position    = $args['position'];
        $this->option_name = $args['option_name'];

        $this->addSection(['id' => 'default']);

        add_action('admin_init', [$this, 'registerSetting']);
        add_action('admin_menu', [$this, 'addPage']);
    }

    public function getPageURL()
    {
        return add_query_arg('page', $this->menu_slug, admin_url($this->parent_slug));
    }

    public function isCurrentPage()
    {
        global $pagenow;

        $base = $this->parent_slug ? $this->parent_slug : 'admin.php';

        return $pagenow === $base && isset($_GET['page']) && $_GET['page'] === $this->menu_slug;
    }

    public function getFieldId($field)
    {
        return sprintf('%1$s-%2$s', $this->menu_slug, $field);
    }

    public function getFieldName($field)
    {
        return sprintf('%1$s[%2$s]', $this->option_name, $field);
    }

    public function getDefaultOptions()
    {
        $options = [];

        foreach ($this->fields as $field) {
            $options[$field['name']] = $field['default_value'];
        }

        return $options;
    }

    public function getOption($name)
    {
        $options = get_option($this->option_name, $this->getDefaultOptions());

        if (isset($options[$name])) {
            return $options[$name];
        }

        return null;
    }

    public function addSection($args)
    {
        $section = wp_parse_args($args, [
            'id'       => '',
            'title'    => '',
            'callback' => null,
        ]);

        $this->sections[$section['id']] = $section;
    }

    public function addField($args)
    {
        $field = wp_parse_args($args, [
            'name'          => '',
            'label'         => '',
            'default_value' => '',
            'type'          => 'text',
            'section'       => 'default',
            'description'   => '',
        ]);

        $this->fields[$field['name']] = $field;
    }

    public function addPage()
    {
        $this->page_hook = add_submenu_page(
            $this->parent_slug,
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            [$this, 'renderPage'],
            $this->position
        );
    }

    public function renderPage()
    {
        ?>

        <div class="wrap">

            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form action="options.php" method="post">
                <?php settings_fields($this->menu_slug); ?>
                <?php do_settings_sections($this->menu_slug); ?>
                <?php submit_button(); ?>
            </form>

        </div>

        <?php
    }

    public function sanitizeOptions($input)
    {
        foreach ($this->fields as $field) {
            $key = $field['name'];
            $value = isset($input[$key]) ? $input[$key] : null;
            $input[$key] = apply_filters('my_google_client_sanitize_field_value/type=' . $field['type'], $value, $field);
        }

        return $input;
    }

    public function registerSetting()
    {
        register_setting($this->menu_slug, $this->option_name, [
            'sanitize_callback' => [$this, 'sanitizeOptions'],
        ]);

        foreach ($this->sections as $section) {
            add_settings_section($section['id'], $section['title'], $section['callback'], $this->menu_slug);
        }

        foreach ($this->fields as $field) {
            add_settings_field($field['name'], $field['label'], [$this, 'renderField'], $this->menu_slug, $field['section'], $field + [
                'label_for' => $this->getFieldId($field['name']),
            ]);
        }
    }

    public function unregisterSetting()
    {
        unregisterSetting($this->menu_slug, $this->option_name);
    }

    public function renderField($field)
    {
        $args = [
            'id'    => $this->getFieldId($field['name']),
            'name'  => $this->getFieldName($field['name']),
            'value' => $this->getOption($field['name']),
        ] + $field;

        $args = apply_filters("my_google_client_prepare_field/type={$field['type']}", $args, $field);

        do_action("my_google_client_render_field/type={$field['type']}", $args);

        if ($args['description']) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }
}
