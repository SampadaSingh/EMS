<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f6fa;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #17153B;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;

        }

        .menu-item:hover {
            background-color: rgb(75, 64, 141);
        }

        .menu-item.active {
            background-color: rgb(81, 64, 179);
        }

        .menu-item img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .menu-item span {
            color: white;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        .calendar {
            margin-top: 200px;
            padding: 20px;
            background-color: #2a2679;
            border-radius: 10px;
            text-align: center;
        }

        .calendar h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: white;
        }

        .calendar p {
            color: #ddd;
            font-size: 14px;
        }

        #currentDate {
            font-size: 14px;
            color: #ddd;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <div class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/dashboard.png" alt="Dashboard">
                <span>Dashboard</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <a href="manageUsers.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/conference.png" alt="Users">
                <span>Manage Users</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
            <a href="manageEvents.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/calendar.png" alt="Events">
                <span>Manage Events</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <a href="reports.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/report-card.png" alt="Reports">
                <span>Reports</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/settings.png" alt="Settings">
                <span>Settings</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'logout.php' ? 'active' : ''; ?>">
            <a href="../php/logout.php">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/logout-rounded.png" alt="Logout">
                <span>Logout</span>
            </a>
        </div>

        <div class="calendar">
            <h3>Calendar</h3>
            <p id="currentDate"></p>
        </div>
    </div>

</body>
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

</html>