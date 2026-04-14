<?php

function dd(...$vars)
{
  echo '
    <style>
      .dd-container {
        font-family: monospace;
      }
      .dd-accordion {
        background: #f4f4f4;
        border: 1px solid #ddd;
        margin-bottom: 5px;
        border-radius: 4px;
      }
      .dd-header {
        padding: 10px;
        cursor: pointer;
        background: #e2e2e2;
        font-weight: bold;
      }
      .dd-content {
        display: none;
        padding: 10px;
        border-top: 1px solid #ddd;
        background: #fff;
      }
    </style>

    <div class="dd-container">
  ';

  foreach ($vars as $index => $var) {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $line = file($backtrace[0]['file'])[$backtrace[0]['line'] - 1];
    preg_match('/dd\((.+)\)/', $line, $matches);

    $names = isset($matches[1]) ? explode(',', $matches[1]) : [];
    $name = isset($names[$index]) ? trim($names[$index]) : "Variable " . ($index + 1);

    echo '
      <div class="dd-accordion">
        <div class="dd-header" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === \'block\' ? \'none\' : \'block\'">
          ' . htmlspecialchars($name) . '
        </div>
        <div class="dd-content">
          <pre>';
            print_r($var);
            echo '
          </pre>
        </div>
      </div>';
  }

  echo '</div>';

  echo '
    <script>
        // All accordions are closed by default (already handled via CSS)
    </script>
    ';
  die();
}

function sanitize($data)
{
  return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function redirect($path)
{
  header("Location: $path");
  exit;
}
