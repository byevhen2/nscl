<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Notices;

/**
 * Don't forget to register the notice:
 * <pre>
 *     $notice = new AdminNotice(...);
 *     ...
 *     $notice->register();
 * </pre>
 */
class AdminNotice
{
    protected $level = 'info';
    protected $message = '';
    protected $afterHtml = '';
    protected $isDismissible = true;

    public function __construct(string $message, $level = 'info', array $settings = null)
    {
        $defaults = ['is-dismissible' => true, 'before-end' => ''];

        // With two parameters second can be settings
        if (!is_null($settings)) {
            $settings = wp_parse_args($settings, $defaults);
        } else {
            $settings = wp_parse_args($level, $defaults);
        }

        // Check level
        if (is_array($level) || !in_array($level, ['success', 'info', 'warning', 'error'])) {
            $level = ($level === 'warn') ? 'warning' : 'info';
        }

        $this->level = $level;
        $this->message = $message;
        $this->afterHtml = $settings['before-end'];
        $this->isDismissible = $settings['is-dismissible'];
    }

    public function register()
    {
        add_action('admin_notices', [$this, 'render']);
    }

    public function render()
    {
        $class = "notice notice-{$this->level}";

        if ($this->isDismissible) {
            $class .= ' is-dismissible';
        }

        ?>
        <div class="<?php echo esc_attr($class); ?>">
            <p><?php echo $this->message; ?></p>
            <?php echo $this->afterHtml; ?>
        </div>
        <?php
    }
}
