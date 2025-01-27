<div class="sidebar">
        <h2>EMS</h2>
        <div class="menu-item">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>My Events</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="participants.php">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>My Account</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="../php/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </a>
        </div>
        <div class="calendar">
            <h3>Calendar</h3>
            <p id="currentDate"></p>
        </div>
    </div>

    <script>
    function updateCalendar() {
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const today = new Date().toLocaleDateString('en-US', options);
            document.getElementById('currentDate').innerText = today;
        }
        updateCalendar();
    </script>