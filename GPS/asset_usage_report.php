<?php
include 'auth.php';  // Authentication
include 'db_connect.php';  // Database connection
include 'theme.php';
// SQL query to get asset usage data
$sql = "
SELECT
    a.asset_id,
    a.asset_name,
    COUNT(t.transaction_id) AS usage_count,
    MAX(t.transaction_date) AS last_used,
    CASE
        WHEN MAX(t.transaction_date) < NOW() - INTERVAL 30 DAY THEN 'Idle'
        ELSE 'In Use'
    END AS asset_status
FROM
    assets a
LEFT JOIN
    transactions t ON a.asset_id = t.asset_id
GROUP BY
    a.asset_id, a.asset_name
ORDER BY
    usage_count DESC, last_used DESC;
";

// Run the query and check for errors
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Usage Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Custom styles for a darker theme */
        .sortable:hover {
            cursor: pointer;
            opacity: 0.85;
        }

        .ascending::after {
            content: " 🔼";
        }

        .descending::after {
            content: " 🔽";
        }
        .theme-light {
            background-color: #f4f4f4;
            color: black;
        }

        /* Dark theme styles */
        .theme-dark {
            background-color: #1f2937;
            color: white;
        }
    </style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">
 <!-- Header Section with Navigation at the Top -->
    <header class="shadow-md w-full" style="background-color: #1f1f2e;">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <!-- Logo or User Avatar -->
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center text-xl font-semibold">
                    <span><?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?></span> <!-- Initials of user -->
                </div>
                <h1 class="text-3xl font-semibold text-white">GPS TRACKING SYSTEM</h1>
            </div>
            
           <?php include 'nav.php';?>
    </header>

    <div class="container mx-auto p-8 max-w-7xl">

        

        <?php if ($result->num_rows > 0) { ?>

            <div class="overflow-x-auto bg-gray-800 shadow-xl rounded-lg p-6">
                <h1 class="text-4xl font-extrabold text-white text-center mb-6">Asset Usage Report</h1>
                <table id="assetTable" class="min-w-full text-gray-300">
                    <thead class="bg-gradient-to-r from-indigo-800 via-purple-800 to-indigo-600 text-white">
                        <tr>
                            <th class="py-3 px-6 text-left text-sm font-semibold sortable" data-column="asset_id">Asset ID</th>
                            <th class="py-3 px-6 text-left text-sm font-semibold sortable" data-column="asset_name">Asset Name</th>
                            <th class="py-3 px-6 text-left text-sm font-semibold sortable" data-column="usage_count">Usage Count</th>
                            <th class="py-3 px-6 text-left text-sm font-semibold sortable" data-column="last_used">Last Used</th>
                            <th class="py-3 px-6 text-left text-sm font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="py-3 px-6"><?php echo $row['asset_id']; ?></td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td class="py-3 px-6"><?php echo $row['usage_count']; ?></td>
                                <td class="py-3 px-6"><?php echo date('Y-m-d H:i:s', strtotime($row['last_used'])); ?></td>
                                <td class="py-3 px-6">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        <?php echo ($row['asset_status'] == 'In Use') ? 'bg-green-700 text-green-100' : 'bg-yellow-700 text-yellow-100'; ?>">
                                        <?php echo $row['asset_status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-center text-xl text-gray-400 mt-4">No data available.</p>
        <?php } ?>

    </div>

    <script>
        // JavaScript for sorting functionality
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById('assetTable');
            const headers = table.querySelectorAll('.sortable');
            
            let currentSortColumn = '';
            let currentSortDirection = 'ascending';

            // Add click event for sorting
            headers.forEach(header => {
                header.addEventListener('click', function() {
                    const column = header.dataset.column;
                    if (currentSortColumn !== column) {
                        currentSortColumn = column;
                        currentSortDirection = 'ascending';
                    } else {
                        currentSortDirection = (currentSortDirection === 'ascending') ? 'descending' : 'ascending';
                    }
                    
                    sortTable(table, column, currentSortDirection);
                    updateHeaderArrow(header);
                });
            });

            function sortTable(table, column, direction) {
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const index = Array.from(headers).indexOf(table.querySelector(`[data-column="${column}"]`));
                
                rows.sort((rowA, rowB) => {
                    const cellA = rowA.cells[index].innerText.trim();
                    const cellB = rowB.cells[index].innerText.trim();

                    // Numeric sort for usage count and dates
                    if (isNumeric(cellA) && isNumeric(cellB)) {
                        return direction === 'ascending' ? cellA - cellB : cellB - cellA;
                    } else {
                        return direction === 'ascending' ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                    }
                });

                // Append sorted rows back to the table
                rows.forEach(row => table.querySelector('tbody').appendChild(row));
            }

            // Check if a string is numeric
            function isNumeric(value) {
                return !isNaN(value) && !isNaN(parseFloat(value));
            }

            // Update the header arrow based on the sort direction
            function updateHeaderArrow(header) {
                headers.forEach(h => {
                    h.classList.remove('ascending', 'descending');
                });

                header.classList.add(currentSortDirection);
            }
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>
