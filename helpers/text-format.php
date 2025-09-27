<?php
function formatTitle($text) {
  // Trim whitespace, collapse multiple spaces, escape HTML, and capitalize each word
  $clean = trim(preg_replace('/\s+/', ' ', $text));
  return ucwords(htmlspecialchars($clean));
}

function sentenceCase($text)
{
  // Normalize casing
  $text = strtolower(trim($text));

  // Capitalize first letter of each sentence
  $text = preg_replace_callback('/(?:^|[.!?]\s+)(\w)/', function ($matches) {
    return strtoupper($matches[0]);
  }, $text);

  // Auto-add period if missing
  if (!preg_match('/[.!?]$/', $text)) {
    $text .= '.';
  }

  return $text;
}