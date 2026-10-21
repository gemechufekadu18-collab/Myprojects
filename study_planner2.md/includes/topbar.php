<div class="topbar">

    <div class="topbar-title">
        Study Planner
    </div>

    <div class="user-area">

        <div>
            <div class="user-name">
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </div>
        </div>

        <div class="user-avatar">
            <?php
                echo strtoupper(
                    substr($_SESSION["full_name"], 0, 1)
                );
            ?>
        </div>

    </div>

</div>