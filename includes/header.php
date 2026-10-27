<?php
if (!isset($base_url)) {
    $base_url = '';
}
$page_title = isset($page_title) ? $page_title . " - X PPLG B" : "Project Web Kelas - X PPLG B";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <!-- Google Fonts: Roboto & Outfit for crisp modern typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>style/style.css">
</head>
<body>
