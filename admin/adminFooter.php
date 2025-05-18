</div> <!-- /#main - This closes the div opened in dashboard.php/viewMyths.php etc -->
  </div> <!-- /#dashboardWrapper - This closes the div opened in admin_header.php -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS - Use the calculated base URL from header (or recalculate/hardcode) -->
  <!-- In adminFooter.php -->
  <!-- Custom JS - Use the calculated base URL from header (or recalculate/hardcode) -->
  <?php
    // Use the variable potentially already defined in adminHeader.php if scope allows,
    // otherwise recalculate as done here.
    if (!isset($base_asset_url)) {
        // Recalculate if not available from header scope
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_asset_url = $protocol . $host . rtrim(dirname($script_dir), '/') . '/assets';
    }
  ?>
  <!-- *** CORRECTED PATH BELOW *** -->
  <!-- In adminFooter.php - TEMPORARY TEST -->
<script src="../assets/js/adminDashboard.js"></script>
  <!-- You can add page-specific JS includes here if needed -->

</body>
</html>