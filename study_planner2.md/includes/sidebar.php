<div class="sidebar">

    <div class="logo">
        Study<span>Planner</span>
    </div>

    <div class="nav-title">
        Main Menu
    </div>

    <ul class="nav-menu">

        <li>
            <a href="/study_planner2/dashboard/index.php">
                <span class="nav-icon">🏠</span>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/subjects/index.php">
                <span class="nav-icon">📚</span>
                <span>Subjects</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/study_plans/index.php">
                <span class="nav-icon">📋</span>
                <span>Study Plans</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/tasks/index.php">
                <span class="nav-icon">✅</span>
                <span>Tasks</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/timetable/index.php">
                <span class="nav-icon">📅</span>
                <span>Timetable</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/study_sessions/index.php">
                <span class="nav-icon">⏱️</span>
                <span>Study Sessions</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/progress/index.php">
                <span class="nav-icon">📈</span>
                <span>Progress</span>
            </a>
        </li>

        <li>
            <a href="/study_planner2/notifications/index.php">
                <span class="nav-icon">🔔</span>
                <span>Notifications</span>
            </a>
        </li>

    </ul>

    <br>

    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>

        <div class="nav-title">
            Administration
        </div>

        <ul class="nav-menu">

            <li>
                <a href="/study_planner2/admin/index.php">
                    <span class="nav-icon">⚙️</span>
                    <span>Admin Panel</span>
                </a>
            </li>

        </ul>

    <?php endif; ?>

    <br>

    <ul class="nav-menu">

        <li>
            <a href="/study_planner2/auth/logout.php">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </li>

    </ul>

</div>