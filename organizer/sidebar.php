<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        .sidebar {
            width: 270px;
            background-color: #17153B;
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .menu-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

 
        .menu-item:hover {
            background-color: rgb(75, 64, 141);
        }

        .menu-item.active {
            background-color: rgb(81, 64, 179);
        }
        

        .menu-item img {
            width: 24px;
            height: 24px;
            margin-right: 15px;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
        }


        .calendar {
            margin-top: 230px;
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
        <h2>EMS</h2>
        <?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
        <div class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>My Events</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'participants.php' ? 'active' : ''; ?>">
            <a href="participants.php">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'account.php' ? 'active' : ''; ?>">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>My Account</span>
            </a>
        </div>
        <div class="menu-item <?php echo $current_page == 'logout.php' ? 'active' : ''; ?>">
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

