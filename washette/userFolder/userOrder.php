<?php

// Start the session
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Checks if there is a user logged in
if (!isset($_SESSION['CustomerID'])) {

  // If not logged in, redirect to login page
  header("Location: login.php");
  exit();

}

// Connect to Database
require_once('../classes/database.php');
$con = new database();

// Set Customer Name
$_SESSION['CustomerName'] = $_SESSION['CustomerFN'] . " " . $_SESSION['CustomerLN'];
$CustomerName = $_SESSION['CustomerName'];

// Initialize a variable to hold the SweetAlert configuration
$sweetAlertConfig = "";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Orders - Washette Laundromat</title>
  <link rel="icon" type="image/png" href="../img/icon.png" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="user.css" />
</head>

<body>
  <!-- particles background -->
  <div id="tsparticles" style="
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -1;
      "></div>
  <!-- main order content -->
  <div class="order-card-container">
    <!-- Latest Laundry Transaction Card -->
    <div class="card p-4 mb-4 no-hover-effect">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-title mb-0">
          <i class="fas fa-star me-2"></i>Latest Laundry Transaction
        </div>
        <button
          type="button"
          class="btn btn-sm filter-btn"
          style="background: #395c58; color: #baebe6"
          onclick="window.history.back();"
        >
          <i class="fas fa-arrow-left me-1"></i>Back
        </button>
      </div>

      <div class="latest-transaction-details">

        <?php

        // Fetch the latest transaction data for the logged-in customer
        $transaction_data = $con->getLatestTransaction($_SESSION['CustomerID']);

        if ($transaction_data) {
          echo '<b>Transaction ID:</b> ' . $transaction_data['TransactionID'] . '<br />';
          echo '<b>Customer ID:</b> ' . $transaction_data['CustomerID'] . '<br />';
          echo '<b>Customer Name:</b> ' . $CustomerName . '<br />';
          echo '<b>Service Type:</b> ' . htmlspecialchars($transaction_data['Services']) . '<br />';
          echo '<b>Status:</b> <span class="glass-badge pending">' . htmlspecialchars($transaction_data['StatusName']) . '</span><br />';
          echo '<b>Payment Method:</b> ' . htmlspecialchars($transaction_data['PMethodName']) . '<br />';
          echo '<b>Total:</b> ₱' . number_format($transaction_data['TransacTotalAmount'], 2) . '<br />';
          echo '<span style="display:inline-block; font-size:0.93em; margin-top:0.7em; opacity:0.85;">
                  <i class="fa-regular fa-calendar-alt me-1"></i>' . $transaction_data['FormattedDate'] . '
              </span>';
        }
        ?>

      </div>
    </div>
    <!-- Laundry Transaction History Card -->
    <div class="card p-4 no-hover-effect">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-title mb-0">
          <i class="fas fa-clock me-2"></i>Laundry Transaction History
        </div>
        <div class="d-flex align-items-center" style="gap: 10px">
          <button type="button" class="btn btn-sm filter-btn" data-bs-toggle="modal" data-bs-target="#filterModal"
            style="background: #395c58; color: #baebe6">
            <i class="fas fa-filter me-1"></i>Filter
          </button>
        </div>
      </div>
      <div class="orders table-responsive">
        <table class="transaction-table align-middle" id="ordersTable">
          <thead>
            <tr>
              <th scope="col">Transaction ID</th>
              <th scope="col">Date</th>
              <th scope="col">Service</th>
              <th scope="col">Status</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody>
            <!-- Fetch transactions from the database -->
            <?php

            // Prepare the SQL statement to fetch transactions
            $transactions = $con->getTransactions($_SESSION['CustomerID']);

            // For each transactions, create a table row
            // Assuming $transactions is an array of associative arrays
            foreach ($transactions as $transaction) {
              ?>

              <tr>
                <td>

                  <?php echo $transaction['TransactionID']; ?>
                </td>

                </td>
                <td>

                  <?php echo $transaction['FormattedDate']; ?>

                </td>
                <td>

                  <?php echo $transaction['Services']; ?>

                </td>
                <td>

                  <span class="glass-badge completed"><?php echo $transaction['Status']; ?></span>

                </td>
                <td>

                  <?php echo $transaction['TransacTotalAmount']; ?>

                </td>
              </tr>
            </tbody>

            <!-- Close the foreach loop -->
            <?php

            }

            ?>
        </table>
      </div>
    </div>
  </div>
  <!-- Filter Modal (copied from userHome.html) -->
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">
            Filter Orders by Status
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="filterForm">
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Pending" id="statusPending"
                checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusPending" style="color: #395c58">Pending</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Completed" id="statusCompleted"
                checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusCompleted" style="color: #395c58">Completed</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Cancelled" id="statusCancelled"
                checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusCancelled" style="color: #395c58">Cancelled</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="In Progress" id="statusInProgress"
                checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusInProgress" style="color: #395c58">In Progress</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Failed" id="statusFailed"
                checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusFailed" style="color: #395c58">Failed</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="applyFilterBtn" class="btn" style="background: #395c58; color: #fff">
            Apply Filter
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- scripts for frontend functionality -->
  <script src="userscript.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
  <script src="/particles.js"></script>
</body>

</html>