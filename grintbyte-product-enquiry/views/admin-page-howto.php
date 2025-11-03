<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
    <h1>📘 How-To: GrintByte Product Enquiry</h1>
    <p>This page provides a simple overview and developer reference for the GrintByte Product Enquiry plugin.</p>

    <hr>

    <h2>🧩 Overview</h2>
    <p>
        This plugin allows customers to send product enquiries directly from WooCommerce product pages. 
        Each enquiry is logged in the database and optionally emailed to the store admin.
    </p>

    <h2>🧰 Workflow</h2>
    <ol>
        <li>Customer fills the enquiry form on the product page.</li>
        <li>Form data is validated and stored in <code>wp_gbe_enquiries</code>.</li>
        <li>Each product related to the enquiry is stored in <code>wp_gbe_enquiry_items</code>.</li>
        <li>An email notification is sent to the admin (configured under Settings).</li>
        <li>Admin can view and manage enquiries under “Product Enquiry → Enquiry List”.</li>
    </ol>

    <h2>📂 Database Tables</h2>
    <pre><code>wp_gbe_enquiries
- id
- fullname
- email
- phone_number
- company
- website
- status
- notes
- created_at

wp_gbe_enquiry_items
- id
- enquiry_id
- product_id
- variation_id
- product_name
- variant_text
</code></pre>

    <h2>🧑‍💻 Developer Hooks</h2>
    <pre><code>// Triggered after enquiry saved
do_action( 'gbe_enquiry_saved', $data );

// Filter email subject before sending
apply_filters( 'gbe_email_subject', $subject, $data );

// Filter email body before sending
apply_filters( 'gbe_email_body', $body, $data );
</code></pre>

    <h2>📨 Email Template Placeholders</h2>
    <ul>
        <li><code>{name}</code> — Customer name</li>
        <li><code>{email}</code> — Customer email</li>
        <li><code>{phone}</code> — Customer phone</li>
        <li><code>{product}</code> — Product name</li>
        <li><code>{company}</code>, <code>{website}</code> — Optional fields</li>
    </ul>

    <h2>🧭 File Structure</h2>
    <pre><code>/includes/class-admin-page.php   → Handles admin menu and pages
/views/admin-page-list.php       → Enquiry listing page
/views/admin-page-setting.php    → Settings page
/views/admin-page-howto.php      → How-To (this page)
</code></pre>

    <hr>
    <p><em>Last updated:</em> <?php echo esc_html( date('Y-m-d') ); ?></p>
</div>
