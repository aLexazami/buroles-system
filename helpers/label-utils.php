<?php
function getAccessLabel(string|false|null $access): string
{
  static $labels = [
    'owner'   => 'Owner',
    'editor'  => 'Can Edit',
    'comment' => 'Can Comment',
    'view'    => 'View Only',
  ];

  return $labels[$access] ?? 'No Access';
}

function getAccessColor(string|false $access): string
{
  return match ($access) {
    'owner'   => 'emerald',
    'editor'  => 'blue',
    'comment' => 'orange',
    'view'    => 'gray',
    default   => 'red',
  };
}