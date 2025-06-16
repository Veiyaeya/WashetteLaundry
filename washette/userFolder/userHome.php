<?php
// Start the session
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Checks if there is a user logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../login.php");
    exit();
}

// Connect to Database
require_once('../classes/database.php');
$con = new database();

// Set Customer Name
$_SESSION['CustomerName'] = $_SESSION['CustomerFN'] . " " . $_SESSION['CustomerLN'];
// Debug: Verify session data
// var_dump($_SESSION['CustomerFN'], $_SESSION['CustomerLN'], $_SESSION['CustomerName']); // Uncomment for debugging

// Initialize a variable to hold the SweetAlert configuration
$sweetAlertConfig = "";

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    $sweetAlertConfig = "
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Logout Successful',
        text: 'You have been logged out successfully.',
        confirmButtonText: 'Login Again'
      }).then(() => {
        window.location.href = '../logout.php';
      });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customer Home - Washette Laundromat</title>
  <link rel="icon" type="image/png" href="/washette/img/icon.png" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="user.css" />
  <style>
    /* Minimal inline styles to avoid conflicts with user.css */
    #mainContent.modal-blur {
      filter: blur(2px);
      transition: filter 0.3s ease;
    }
    #mainContent.modal-blur-fadeout {
      filter: blur(0);
      transition: filter 0.3s ease;
    }
    .modal, .modal-content {
      filter: none !important;
      backdrop-filter: none !important;
      z-index: 1050;
    }
    .modal-backdrop {
      z-index: 1040;
    }
    #background-glow {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: -2;
      transition: background 0.2s ease;
    }
    .bounce-animate {
      animation: bounce 0.3s ease;
    }
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    .glass-badge.completed, .glass-badge.pending, .glass-badge.in-progress, .glass-badge.cancelled {
      background: rgba(57, 92, 88, 0.2);
      color: #395c58;
      padding: 5px 10px;
      border-radius: 12px;
      backdrop-filter: blur(5px);
    }
    .about-card {
      padding: 15px !important;
      margin-bottom: 0 !important;
      background: rgba(225, 243, 242, 0.27);
      border: 1.5px solid #bfe6e3;
      border-radius: 10px;
    }
    .nav-menu {
      position: absolute;
      top: 60px;
      right: 10px;
      background: rgba(217, 242, 239, 0.9);
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      backdrop-filter: blur(5px);
      z-index: 1000;
      width: 200px;
      padding: 10px 0;
      display: none;
    }
    .nav-menu.show {
      display: block;
    }
    .nav-menu a, .nav-menu button {
      display: block;
      padding: 8px 16px;
      color: #395c58;
      font-size: 0.95rem;
      text-decoration: none;
      background: none;
      border: none;
      width: 100%;
      text-align: left;
      transition: background 0.3s ease, color 0.3s ease;
    }
    .nav-menu a:hover, .nav-menu button:hover {
      background: rgba(57, 92, 88, 0.2);
      color: #baebe6;
    }
    @media (max-width: 768px) {
      .nav-menu {
        width: 180px;
        right: 5px;
      }
    }
    .transaction-table .no-data {
      text-align: center;
      color: #395c58;
      padding: 20px;
    }
  </style>
</head>
<body>
  <!-- Particles background -->
  <div id="tsparticles" style="position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: -1;"></div>

  <!-- Main Content Wrapper -->
  <div id="mainContent">
    <!-- Header -->
    <div class="header d-flex align-items-center justify-content-between rounded-0 px-3 py-2">
      <div class="d-flex align-items-center" style="margin-left: 12px;">
        <span style="font-size: 1.2rem; color: #395c58; font-weight: 700;">Home</span>
      </div>
      <div class="profile-wrapper position-relative">
        <div id="profileCard" class="d-flex align-items-center px-3 py-2" title="Profile Menu">
          <span id="profileName" style="font-size: 1rem; font-weight: 700; color: #395c58; margin-right: 8px;">
            <?php echo htmlspecialchars($_SESSION['CustomerName']); ?>
          </span>
          <img id="profileIcon" src="/washette/img/profile.png" alt="Profile" class="profile-picture" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;" />
        </div>
        <!-- Navigation Menu -->
        <div id="navMenu" class="nav-menu">
          <a href="/washette/fpassword.php" class="nav-link">Change Password</a>
          <form id="logoutForm" method="POST" action="../logout.php" class="m-0">
            <button type="submit" name="logout" value="1" class="nav-link">Logout</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Main Home Content -->
    <div class="container py-4" style="flex: 1 0 auto;">
      <!-- Recent Orders -->
      <div class="card p-3 mb-4 no-hover-effect" style="min-height: 60vh;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
              <i class="fas fa-clock me-2"></i>Recent Orders
            </h5>
            <button type="button" class="btn btn-sm filter-btn" data-bs-toggle="modal" data-bs-target="#filterModal">
              <i class="fas fa-filter me-1"></i>Filter
            </button>
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
                <?php
                  $transactions = $con->getTransactions($_SESSION['CustomerID']);
                  if (!empty($transactions)) {
                    foreach ($transactions as $transaction) {
                ?>
                    <tr>
                      <td><?php echo htmlspecialchars($transaction['TransactionID']); ?></td>
                      <td><?php echo htmlspecialchars($transaction['FormattedDate']); ?></td>
                      <td><?php echo htmlspecialchars($transaction['Services']); ?></td>
                      <td>
                        <span class="glass-badge <?php echo strtolower(str_replace(' ', '-', $transaction['Status'])); ?>">
                          <?php echo htmlspecialchars($transaction['Status']); ?>
                        </span>
                      </td>
                      <td><?php echo htmlspecialchars($transaction['TransacTotalAmount']); ?></td>
                    </tr>
                <?php
                    }
                  } else {
                ?>
                    <tr>
                      <td colspan="5" class="no-data">No transactions found.</td>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Order List and Services -->
      <div class="row g-4">
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="orderListCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-receipt"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">Order List</h5>
                <p class="card-text mb-0">View all your orders</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="servicesCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-cogs"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">Services</h5>
                <p class="card-text mb-0">View our laundry services</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="aboutUsCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-info-circle"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">About Us</h5>
                <p class="card-text mb-0">Learn about our services</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="locationCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-map-marker-alt"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">Location</h5>
                <p class="card-text mb-0">See our location</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="termsCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-file-contract"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">Terms & Agreements</h5>
                <p class="card-text mb-0">View our terms and policies</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card about-card p-3 position-relative" id="contactUsCard" style="cursor: pointer">
            <div class="card-body d-flex align-items-center">
              <span class="card-icon me-3"><i class="fas fa-envelope"></i></span>
              <div class="card-content flex-grow-1">
                <h5 class="card-title mb-1">Contact Us</h5>
                <p class="card-text mb-0">Get our contact information</p>
              </div>
              <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer-washette">© 2025 Washette Laundromat</div>
  </div>

  <!-- Profile Modal -->
  <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="profileModalLabel">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="firstNameInput" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstNameInput" value="<?php echo htmlspecialchars($_SESSION['CustomerFN']); ?>" />
            </div>
            <div class="mb-3">
              <label for="lastNameInput" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastNameInput" value="<?php echo htmlspecialchars($_SESSION['CustomerLN']); ?>" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="saveProfileBtn" class="btn" style="background: #395c58; color: #fff">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Modal -->
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Filter Orders by Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="filterForm">
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Completed" id="statusCompleted" checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusCompleted" style="color: #395c58">Completed</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="Cancelled" id="statusCancelled" checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusCancelled" style="color: #395c58">Cancelled</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input status-checkbox" type="checkbox" value="In Progress" id="statusInProgress" checked />
              <span class="custom-checkmark"></span>
              <label class="form-check-label" for="statusInProgress" style="color: #395c58">In Progress</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="applyFilterBtn" class="btn" style="background: #395c58; color: #fff">Apply Filter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Us Modal -->
  <div class="modal fade" id="contactUsModal" tabindex="-1" aria-labelledby="contactUsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="contactUsModalLabel">Contact Us</h5>
        </div>
        <div class="modal-body">
          <div style="color: #395c58; font-size: 1.05rem; font-weight: 400; line-height: 1.7; padding: 18px 24px; margin-bottom: -2em;">
            <div class="mb-3" style="display: flex; align-items: center; gap: 10px">
              <i class="fas fa-phone fa-lg" style="color: #395c58; min-width: 24px; text-align: center"></i>
              <span style="font-family: inherit">+63 927 701 0505</span>
            </div>
            <div class="mb-3" style="display: flex; align-items: center; gap: 10px">
              <i class="fab fa-facebook fa-lg" style="color: #395c58; min-width: 24px; text-align: center"></i>
              <a href="https://www.facebook.com/washettelaundromat" target="_blank" rel="noopener noreferrer" style="font-family: inherit; color: #395c58; text-decoration: none; font-weight: 500; cursor: pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                Washette Laundromat
              </a>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn filter-btn" style="border-radius: 18px; font-weight: 500; font-size: 1rem; padding: 0.6rem 2.2rem; background: #466c69; color: #fff; border: none; box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Location Modal -->
  <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="locationModalLabel">Our Location</h5>
        </div>
        <div class="modal-body">
          <div style="color: #395c58; font-size: 1.05rem; font-weight: 400; line-height: 1.7; padding: 10px 18px; margin-bottom: 0;">
            <div style="display: flex; align-items: flex-start; gap: 10px">
              <i class="fas fa-map-marker-alt fa-lg" style="color: #395c58; min-width: 24px; text-align: center; margin-top: 12px;"></i>
              <span style="font-family: inherit">
                Unit 8 Blk4 Lot15 Dona Aurora St.<br />
                City Park Subdivision Brgy Sabang<br />
                Lipa City Batangas
              </span>
            </div>
            <div style="margin-top: 18px; text-align: center; display: flex; justify-content: center; align-items: center;">
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d484.0120904635915!2d121.16690879877665!3d13.952846149109217!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd6dd158c3d7c1%3A0x351b75b1d8884624!2sWashette%20Laundry%20Services!5e0!3m2!1sen!2sph!4v1749284420290!5m2!1sen!2sph"
                width="300" height="300" style="border: 0; border-radius: 10px" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn filter-btn" style="border-radius: 18px; font-weight: 500; font-size: 1rem; padding: 0.6rem 2.2rem; background: #395c58; color: #fff; border: none; box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- About Us Modal -->
  <div class="modal fade" id="aboutUsModal" tabindex="-1" aria-labelledby="aboutUsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="aboutUsModalLabel">About Us</h5>
        </div>
        <div class="modal-body">
          <p style="color: #395c58; font-size: 0.98rem; font-weight: 400; line-height: 1.7; padding: 18px 24px; margin-bottom: 0;">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Pellentesque euismod, nisi eu consectetur cursus, enim erat dictum
            urna, nec dictum sapien enim nec urna. Proin facilisis, velit ac
            sollicitudin cursus, enim erat dictum urna, nec dictum sapien enim
            nec urna.
            <br /><br />
            Sed ut perspiciatis unde omnis iste natus error sit voluptatem
            accusantium doloremque laudantium, totam rem aperiam, eaque ipsa
            quae ab illo inventore veritatis et quasi architecto beatae vitae
            dicta sunt explicabo.
            <br /><br />
            Our mission is to deliver exceptional laundry services with
            professionalism, reliability, and care for every customer.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn filter-btn" style="border-radius: 18px; font-weight: 500; font-size: 1rem; padding: 0.6rem 2.2rem; background: #466c69; color: #fff; border: none; box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Terms Modal -->
  <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="termsModalLabel">Terms & Agreement</h5>
        </div>
        <div class="modal-body" style="color: #395c58; font-size: 1.05rem; line-height: 1.7">
          <ol style="padding-left: 1.3em; margin-bottom: 0">
            <li style="margin-bottom: 1em">
              Washette Laundromat is not liable for any missing socks,
              undergarments, or small items unless you provide a list of your
              laundry before washing.
            </li>
            <li style="margin-bottom: 1em">
              Washette Laundromat is not responsible for changes resulting
              from the normal washing process, such as loss of buttons, items
              left in pockets, shrinkage, discoloration, burns, or rips.
              Please do not include delicate or pre-damaged clothes.
            </li>
            <li style="margin-bottom: 1em">
              Any discrepancies must be reported within 24 hours from pick-up
              time. Complaints after 24 hours will not be entertained.
            </li>
            <li style="margin-bottom: 1em">
              Liability for loss is limited to an amount not exceeding three
              (3) times the laundry package cost.
            </li>
            <li style="margin-bottom: 1em">
              Items not claimed or collected within 30 days will be disposed
              of without prior notice.
            </li>
            <li>
              By using our services, you agree to these laundry rules and
              terms.
            </li>
          </ol>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn filter-btn" style="border-radius: 18px; font-weight: 500; font-size: 1rem; padding: 0.6rem 2.2rem; background: #395c58; color: #fff; border: none; box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Services Modal -->
  <div class="modal fade" id="servicesModal" tabindex="-1" aria-labelledby="servicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="servicesModalLabel">Services</h5>
        </div>
        <div class="modal-body" style="color: #395c58; font-size: 1.05rem; line-height: 1.7; padding: 18px 12px;">
          <div class="container-fluid">
            <div class="fw-bold mb-1">Premium Services</div>
            <div class="row g-3">
              <?php
                $services = $con->getPremiumServicesList();
                foreach ($services as $service) {
              ?>
                <div class="col-6 col-lg-4">
                  <div class="service-card p-3 text-center h-100 rounded-3" style="background-color: rgba(225, 243, 242, 0.27) !important; border: 1.5px solid #bfe6e3">
                    <div class="fw-bold mb-1"><?php echo htmlspecialchars($service['LaundryService_Name']); ?></div>
                    <div style="font-size: 0.97rem"><?php echo htmlspecialchars($service['LaundryService_Desc']); ?></div>
                  </div>
                </div>
              <?php
                }
              ?>
            </div>
            <div class="fw-bold mb-1 mt-3">Regular Services</div>
            <div class="row g-3">
              <?php
                $services = $con->getRegularServicesList();
                foreach ($services as $service) {
              ?>
                <div class="col-6 col-lg-4">
                  <div class="service-card p-3 text-center h-100 rounded-3" style="background-color: rgba(225, 243, 242, 0.27) !important; border: 1.5px solid #bfe6e3">
                    <div class="fw-bold mb-1"><?php echo htmlspecialchars($service['LaundryService_Name']); ?></div>
                    <div style="font-size: 0.97rem"><?php echo htmlspecialchars($service['LaundryService_Desc']); ?></div>
                  </div>
                </div>
              <?php
                }
              ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn filter-btn" style="border-radius: 18px; font-weight: 500; font-size: 1rem; padding: 0.6rem 2.2rem; background: #395c58; color: #fff; border: none; box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="userscript.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
  <script src="/particles.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Modal toggle function
      const openModal = (modalId) => {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId));
        modal.show();
        return modal;
      };

      // Card click events
      const cardModalMap = {
        servicesCard: "servicesModal",
        aboutUsCard: "aboutUsModal",
        locationCard: "locationModal",
        termsCard: "termsModal",
        contactUsCard: "contactUsModal",
      };

      Object.entries(cardModalMap).forEach(([cardId, modalId]) => {
        const card = document.getElementById(cardId);
        if (card) {
          card.addEventListener("click", () => openModal(modalId));
        }
      });

      const orderListCard = document.getElementById("orderListCard");
      if (orderListCard) {
        orderListCard.addEventListener("click", () => {
          window.location.href = "userOrder.php";
        });
      }

      // Profile navigation logic
      const profileCard = document.getElementById("profileCard");
      const navMenu = document.getElementById("navMenu");
      const profileModal = new bootstrap.Modal(document.getElementById("profileModal"));
      const profileName = document.getElementById("profileName");
      const firstNameInput = document.getElementById("firstNameInput");
      const lastNameInput = document.getElementById("lastNameInput");
      const saveProfileBtn = document.getElementById("saveProfileBtn");

      if (profileCard && navMenu) {
        profileCard.addEventListener("click", (e) => {
          e.preventDefault();
          navMenu.classList.toggle("show");
        });

        navMenu.querySelectorAll("a, button").forEach((item) => {
          item.addEventListener("click", () => {
            navMenu.classList.remove("show");
          });
        });

        document.addEventListener("click", (event) => {
          if (!profileCard.contains(event.target) && !navMenu.contains(event.target)) {
            navMenu.classList.remove("show");
          }
        });
      }

      if (saveProfileBtn) {
        saveProfileBtn.addEventListener("click", () => {
          const first = firstNameInput.value.trim();
          const last = lastNameInput.value.trim();
          if (first && last) {
            profileName.textContent = `${first} ${last}`;
            // Send updated name to server
            fetch('update_profile.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `firstName=${encodeURIComponent(first)}&lastName=${encodeURIComponent(last)}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                profileModal.hide();
                Swal.fire({
                  icon: 'success',
                  title: 'Profile Updated',
                  text: 'Your profile has been updated successfully.',
                  confirmButtonText: 'OK'
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: data.message || 'Failed to update profile.',
                  confirmButtonText: 'OK'
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating your profile.',
                confirmButtonText: 'OK'
              });
            });
          } else {
            Swal.fire({
              icon: 'warning',
              title: 'Invalid Input',
              text: 'Please enter both first and last names.',
              confirmButtonText: 'OK'
            });
          }
        });
      }

      // Tooltip initialization
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

      // Filter button animation
      const filterBtn = document.querySelector(".filter-btn");
      if (filterBtn) {
        ["mouseenter", "click"].forEach((event) =>
          filterBtn.addEventListener(event, () => {
            filterBtn.classList.remove("bounce-animate");
            void filterBtn.offsetWidth;
            filterBtn.classList.add("bounce-animate");
          })
        );
      }
    });
  </script>
  <?php echo $sweetAlertConfig; ?>
</body>
</html>