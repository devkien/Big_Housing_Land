<?php
// Root index.php for shared hosting: forward requests to public/index.php
// Upload whole project into public_html and place this file at public_html/index.php
// This keeps the app bootstrap in `public/index.php` unchanged.
require __DIR__ . '/public/index.php';
