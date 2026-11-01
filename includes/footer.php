
<!-- includes/footer.php -->
<!-- 
    This file is included at the bottom of every page.
    It closes the page layout and contains the footer content.
-->

<!-- ===== FOOTER ===== -->
<footer class="bg-dark text-white mt-5 pt-4 pb-3">
    <div class="container">
        <div class="row">

            <!-- Column 1: About -->
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold text-warning">
                    <i class="bi bi-trophy-fill me-2"></i>NSBM SportsHub
                </h5>
                <p class="text-muted small">
                    Your one-stop platform for managing sports clubs, events, 
                    tournaments, and facilities at NSBM Green University.
                </p>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold text-uppercase text-muted mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="clubs.php" class="text-decoration-none text-secondary"><i class="bi bi-chevron-right me-1"></i>View Clubs</a></li>
                    <li><a href="admin/schedule.php" class="text-decoration-none text-secondary"><i class="bi bi-chevron-right me-1"></i>Training Schedule</a></li>
                    <li><a href="admin/tournaments.php" class="text-decoration-none text-secondary"><i class="bi bi-chevron-right me-1"></i>Tournaments</a></li>
                    <li><a href="book_facility.php" class="text-decoration-none text-secondary"><i class="bi bi-chevron-right me-1"></i>Book a Facility</a></li>
                </ul>
            </div>

            <!-- Column 3: Social Media Placeholders -->
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold text-uppercase text-muted mb-3">Follow Us</h6>
                <div class="d-flex gap-3 fs-4">
                    <!-- Placeholder social icons - replace href="#" with real links -->
                    <a href="#" class="text-secondary" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-secondary" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="text-secondary" title="Twitter / X">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="#" class="text-secondary" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
                <p class="text-muted small mt-3">
                    <i class="bi bi-envelope me-1"></i> sports@nsbm.ac.lk
                </p>
            </div>

        </div><!-- end .row -->

        <hr class="border-secondary">

        <!-- Copyright -->
        <div class="text-center text-muted small">
            &copy; <?php echo date('Y'); ?> NSBM SportsHub. All rights reserved. | 
            Developed by Group [Your Group Name] &mdash; NSBM Green University
        </div>
    </div><!-- end .container -->
</footer>
<!-- ===== END FOOTER ===== -->

<!-- Bootstrap 5 JS Bundle (includes Popper.js) - needed for dropdowns, modals, etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
