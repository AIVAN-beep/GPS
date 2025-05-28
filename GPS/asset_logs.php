<?php
include 'auth.php';
include 'db_connect.php';
include 'user_theme.php';

$logs = $conn->query("SELECT * FROM asset_logs ORDER BY timestamp DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />

<title>Asset Activity Logs</title>

<style>
  /* Theme variables */
  .theme-light {
    --bg-color: #f3f4f6;
    --container-bg: #ffffff;
    --text-color: #000000;
    --input-bg: #ffffff;
    --input-border: #cccccc;
    --input-text: #000000;
    --header-bg: #3b82f6;
    --header-text-color: #ffffff;
    --hover-bg: #e5e7eb;
    --modal-overlay-bg: rgba(0, 0, 0, 0.5);
    --modal-bg: #ffffff;
  }
  .theme-dark {
    --bg-color: #1f2937;
    --container-bg: #374151;
    --text-color: #e5e7eb;
    --input-bg: #4b5563;
    --input-border: #6b7280;
    --input-text: #e5e7eb;
    --header-bg: #2563eb;
    --header-text-color: #e5e7eb;
    --hover-bg: #4b5563;
    --modal-overlay-bg: rgba(0, 0, 0, 0.7);
    --modal-bg: #374151;
  }

  body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--bg-color);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
    min-height: 100vh;
  }

  .container {
    background-color: var(--container-bg);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
  }

  .header-bar {
    background-color: var(--header-bg);
    color: var(--header-text-color);
  }

  #searchInput {
    background-color: var(--input-bg);
    border: 1px solid var(--input-border);
    color: var(--input-text);
  }
  #searchInput::placeholder {
    color: var(--input-text);
    opacity: 0.6;
  }
  #searchInput:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
  }

  table {
    border-collapse: collapse;
    width: 100%;
    color: var(--text-color);
  }
  thead tr {
    background-color: var(--header-bg);
    color: var(--header-text-color);
  }
  th, td {
    border: 1px solid var(--input-border);
    padding: 0.5rem 1rem;
  }
  tbody tr:hover {
    background-color: var(--hover-bg);
  }

  /* Badges */
  .badge-added {
    background-color: #22c55e;
    color: white;
  }
  .badge-edited {
    background-color: #eab308;
    color: #111827;
  }
  .badge-deleted {
    background-color: #dc2626;
    color: white;
  }
  .badge-default {
    background-color: #9ca3af;
    color: white;
  }

  /* Modal overlay and content */
  .modal-overlay {
    background-color: var(--modal-overlay-bg);
    transition: opacity 0.3s ease;
    position: fixed;
    inset: 0;
    display: none; /* Hidden by default */
    align-items: center;
    justify-content: center;
    z-index: 50;
  }
  .modal-overlay.flex {
    display: flex;
  }

  .modal-content {
    background-color: var(--modal-bg);
    color: var(--text-color);
    border-radius: 0.75rem; /* rounded-lg */
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    padding: 1.5rem 2rem;
    max-width: 48rem; /* max-w-3xl */
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
    transform: translateY(-20px);
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    outline: none;
  }
  .modal-overlay.flex .modal-content {
    opacity: 1;
    transform: translateY(0);
  }

  /* Close button */
  .modal-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    color: var(--text-color);
    background: transparent;
    border: none;
    font-size: 1.75rem;
    font-weight: 700;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s ease;
    line-height: 1;
    padding: 0;
  }
  .modal-close-btn:hover,
  .modal-close-btn:focus {
    opacity: 1;
    outline: none;
  }

  /* Scrollbar for modal content */
  .modal-content::-webkit-scrollbar {
    width: 8px;
  }
  .modal-content::-webkit-scrollbar-thumb {
    background-color: var(--input-border);
    border-radius: 4px;
  }

  /* Buttons */
  button {
    transition: background-color 0.2s ease;
  }
  button:focus-visible {
    outline: 3px solid #2563eb;
    outline-offset: 2px;
  }

  /* View button consistent styling */
  .btn-view {
    padding: 0.375rem 0.75rem; /* px-3 py-1.5 */
    border-radius: 0.375rem; /* rounded-md */
    font-weight: 600;
    font-size: 0.875rem; /* text-sm */
    color: #2563eb; /* text-blue-600 */
    background-color: #bfdbfe; /* bg-blue-100 */
    cursor: pointer;
  }
  .btn-view:hover {
    background-color: #93c5fd; /* hover:bg-blue-200 */
  }
  .btn-view:focus {
    outline: none;
    box-shadow: 0 0 0 2px #60a5fa; /* focus:ring-2 focus:ring-blue-400 */
  }

  /* Utility spacing for modal details */
  .list-disc li {
    margin-bottom: 0.25rem;
  }
</style>

</head>
<body class="<?php echo htmlspecialchars($theme_class); ?>">
<?php include 'header.php'; ?>

<div class="container mx-auto my-8 max-w-5xl px-4 shadow-lg rounded-lg">
  <div class="flex items-center justify-between px-6 py-4 rounded-t-lg header-bar">
    <h4 class="text-xl font-semibold flex items-center gap-2 select-none">📋 Asset Activity Logs</h4>
    <input
      id="searchInput"
      type="text"
      placeholder="Search logs..."
      class="rounded-md px-3 py-2 w-60"
      autocomplete="off"
      spellcheck="false"
      aria-label="Search logs"
    />
  </div>

  <div class="overflow-x-auto">
    <table id="logsTable" class="min-w-full text-left" role="table" aria-label="Asset activity logs">
      <thead>
        <tr role="row">
          <th scope="col" role="columnheader">Timestamp</th>
          <th scope="col" role="columnheader">Asset ID</th>
          <th scope="col" role="columnheader">Action</th>
          <th scope="col" role="columnheader">Details</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($log = $logs->fetch_assoc()): ?>
        <tr role="row">
          <td role="cell"><?php echo htmlspecialchars($log['timestamp']); ?></td>
          <td role="cell" class="text-blue-600 font-semibold"><?php echo htmlspecialchars($log['asset_id']); ?></td>
          <td role="cell">
            <?php
            $action = strtolower($log['action']);
            $badgeClass = 'badge-default';
            if ($action === 'added') $badgeClass = 'badge-added';
            else if ($action === 'edited') $badgeClass = 'badge-edited';
            else if ($action === 'deleted') $badgeClass = 'badge-deleted';
            ?>
            <span class="inline-block px-2 py-1 rounded text-sm font-semibold <?php echo $badgeClass; ?>">
              <?php echo htmlspecialchars($log['action']); ?>
            </span>
          </td>
          <td role="cell">
            <button
              onclick="openModal('logModal<?php echo $log['id']; ?>')"
              class="btn-view"
              type="button"
              aria-expanded="false"
              aria-controls="logModal<?php echo $log['id']; ?>"
            >View</button>

            <div
  id="logModal<?php echo $log['id']; ?>"
  class="modal-overlay"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modalTitle<?php echo $log['id']; ?>"
  tabindex="-1"
  aria-hidden="true"
>
  <div class="modal-content" tabindex="0">
    <button
      onclick="closeModal('logModal<?php echo $log['id']; ?>')"
      class="modal-close-btn"
      aria-label="Close modal"
      type="button"
    >&times;</button>

    <h3 id="modalTitle<?php echo $log['id']; ?>" class="text-xl font-semibold mb-4">
      Log Details - ID: <?php echo htmlspecialchars($log['id']); ?>
    </h3>

    <table class="w-full text-left table-auto border-collapse">
      <tbody>
        <tr>
          <th class="pr-4 py-2 font-medium">Timestamp</th>
          <td class="py-2"><?php echo htmlspecialchars($log['timestamp']); ?></td>
        </tr>
        <tr>
          <th class="pr-4 py-2 font-medium">Asset ID</th>
          <td class="py-2"><?php echo htmlspecialchars($log['asset_id']); ?></td>
        </tr>
        <tr>
          <th class="pr-4 py-2 font-medium">Action</th>
          <td class="py-2"><?php echo htmlspecialchars($log['action']); ?></td>
        </tr>
      </tbody>
    </table>

    <hr class="my-4 border-gray-400" />

    <h6 class="font-semibold mb-2">Details:</h6>
    <?php
      $decoded = json_decode($log['details'], true);
      if (is_array($decoded)) {
          echo '<table class="w-full text-left table-auto border-collapse">';
          echo '<tbody>';
          foreach ($decoded as $key => $value) {
              echo '<tr>';
              echo '<th class="pr-4 py-2 font-medium">' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</th>';
              echo '<td class="py-2">' . htmlspecialchars($value) . '</td>';
              echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';
      } else {
          echo '<p class="whitespace-pre-wrap">' . htmlspecialchars($log['details']) . '</p>';
      }
    ?>
  </div>
</div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modal.setAttribute('aria-hidden', 'false');

      // Focus modal content for accessibility
      const content = modal.querySelector('.modal-content');
      if(content) content.focus();
    }
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    }
  }

  // Close modal when clicking outside content
  document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
      event.target.classList.remove('flex');
      event.target.classList.add('hidden');
      event.target.setAttribute('aria-hidden', 'true');
    }
  });

  // Close modal on ESC key press
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.flex').forEach(modal => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
      });
    }
  });

  // Search filter
  const searchInput = document.getElementById('searchInput');
  searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#logsTable tbody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
    });
  });
</script>

</body>
</html>
