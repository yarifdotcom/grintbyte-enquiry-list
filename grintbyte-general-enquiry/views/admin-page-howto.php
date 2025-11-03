<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
    <h1>📘 How-To: General Enquiry Plugin</h1>

    <p>This page provides basic guidance for developers who want to customize or extend the <strong>Grintbyte General Enquiry</strong> plugin.</p>

    <h2>1. Table Structure</h2>
    <p>The plugin stores enquiries in a custom table <code>{wp_prefix}gen_enquiries</code>. You can modify or extend the schema in the plugin activator file.</p>

    <h2>2. Email Template</h2>
    <ul>
        <li>Editable from: <strong>Settings → General Enquiry → Settings</strong></li>
        <li>Placeholders supported:
            <code>{name}</code>,
            <code>{email}</code>,
            <code>{phone}</code>,
            <code>{company}</code>,
            <code>{website}</code>
        </li>
    </ul>

    <h2>3. Hooks</h2>
    <pre><code>
do_action( 'gen_enquiry_submitted', $enquiry_id, $data );
    </code></pre>
    <p>Fires after a new enquiry is saved. Use this to trigger third-party integrations.</p>

    <h2>4. Customization</h2>
    <p>You can override email templates or enqueue extra admin scripts using WordPress hooks inside your theme or custom plugin.</p>

    <hr>
    <p><em>Tip:</em> You can freely edit this page’s HTML file at <code>views/admin-page-howto.php</code> to add internal documentation for your team.</p>
</div>
