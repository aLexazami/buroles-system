<?php
function renderHead($pageTitle = 'Burol Elementary School', $noIndex = false, $metaRefresh = null) {
  echo "<!DOCTYPE html>
  <html lang='en'>
  <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>BESIMS [$pageTitle]</title>
    <link rel='icon' href='/assets/img/buroles-colored.png' type='image/png'>
    <link rel='stylesheet' href='/src/styles.css'>
    <link href='https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css' rel='stylesheet'>
    <meta name='description' content='Burol Elementary School Dashboard'>
    <meta name='author' content='Burol Dev Team'>
    <meta name='theme-color' content='#2c3e50'>";
    
  if ($noIndex) {
    echo "<meta name='robots' content='noindex, nofollow'>";
  }

  if ($metaRefresh) {
    echo "<meta http-equiv='refresh' content='" . htmlspecialchars($metaRefresh, ENT_QUOTES, 'UTF-8') . "'>";
  }

  echo "</head>";
}
?>
<!--
renderHead('Login', true); // blocks indexing
renderHead('FAQs');        // allows indexing
-->