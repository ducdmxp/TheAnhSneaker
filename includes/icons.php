<?php
/**
 * Bộ icon SVG nội tuyến (thay cho Font Awesome ~100KB) để trang tải nhanh hơn.
 * Dùng: <?= icon('truck') ?>
 */
function icon(string $name, string $class = ''): string
{
    static $stroke = [
        'truck'   => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3.5V16h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'box'     => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'shield'  => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
        'refresh' => '<path d="M3 12a9 9 0 0 1 15.5-6.2L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"/><path d="M3 21v-5h5"/>',
        'headset' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="2" y="14" width="5" height="7" rx="2"/><rect x="17" y="14" width="5" height="7" rx="2"/>',
        'phone'   => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'chat'    => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.6 8.6 0 0 1-3.8-.9L3 21l1.9-5.2A8.4 8.4 0 1 1 21 11.5z"/>',
        'pin'     => '<path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'arrow-up'    => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'arrow-right' => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        'check'   => '<path d="M20 6L9 17l-5-5"/>',
        'ruler'   => '<path d="M3 17L17 3l4 4L7 21z"/><path d="M7 13l2 2M10 10l2 2M13 7l2 2"/>',
        'tag'     => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'wallet'  => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><path d="M16 15h2"/>',
        'menu'    => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'   => '<path d="M6 6l12 12M18 6L6 18"/>',
        'plus'    => '<path d="M12 5v14M5 12h14"/>',
        'eye'     => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    ];
    static $fill = [
        'star'     => '<path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z"/>',
        'fire'     => '<path d="M12 22c4.1 0 7-2.8 7-6.6 0-3.4-2.3-5.8-3.9-7.8-.4 1.9-1.4 3-2.9 3.5.3-3.4-1.6-6.4-3.4-8.6-.5 4.1-3.8 6.5-3.8 11.7C5 18.9 7.9 22 12 22z"/>',
        'facebook' => '<path d="M14 8h3V4h-3c-2.8 0-4 1.8-4 4.5V11H7v4h3v7h4v-7h3l1-4h-4V8.8c0-.5.3-.8 1-.8z"/>',
        'tiktok'   => '<path d="M16.5 3c.4 2.3 1.9 3.9 4.5 4.1v3.4c-1.7.1-3.2-.4-4.5-1.3v6.3c0 3.6-2.7 6.5-6.3 6.5S4 19.1 4 15.5 6.9 9 10.5 9c.3 0 .6 0 .9.1v3.5a3 3 0 1 0 2.1 2.9V3z"/>',
        'quote'    => '<path d="M4 18v-5.5C4 8 6.5 5.4 10 5l.6 1.8C8.4 7.5 7.4 9 7.3 11H10v7zm10 0v-5.5C14 8 16.5 5.4 20 5l.6 1.8c-2.2.7-3.2 2.2-3.3 4.2H20v7z"/>',
    ];

    $cls = trim('icon ' . $class);
    if (isset($fill[$name])) {
        return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $fill[$name] . '</svg>';
    }
    $paths = $stroke[$name] ?? '';
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}
