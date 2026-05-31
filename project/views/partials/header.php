<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userLogin = $_SESSION['user_login'] ?? '';
?>

<div class="header-wrapper">
    <div class="header-bg">
        <div class="container">
            <div class="pattern-bg">
                <svg preserveAspectRatio="xMidYMid slice" height="100%" width="100%" class="cube-svg"
                    viewBox="0 0 120 104">
                    <defs>
                        <linearGradient y2="100%" x2="100%" y1="0%" x1="0%" id="cube-dark">
                            <stop stop-color="#232526" offset="0%"></stop>
                            <stop stop-color="#414345" offset="100%"></stop>
                        </linearGradient>
                        <linearGradient y2="0%" x2="100%" y1="100%" x1="0%" id="cube-mid">
                            <stop stop-color="#4b6cb7" offset="0%"></stop>
                            <stop stop-color="#182848" offset="100%"></stop>
                        </linearGradient>
                        <linearGradient y2="100%" x2="0%" y1="0%" x1="100%" id="cube-light">
                            <stop stop-color="#a8edea" offset="0%"></stop>
                            <stop stop-color="#fed6e3" offset="100%"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
    <header>
        <div id="header">
            <div id="header-search">
                <form action="/search" method="GET" style="display: flex; width: 100%; position: relative;">
                    <svg class="icon" aria-hidden="true" viewBox="0 0 24 24">
                        <g>
                            <path
                                d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z">
                            </path>
                        </g>
                    </svg>
                    <input type="text" name="q" placeholder="Search" class="input"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
            </div>
            <div id="header-log-out">
                <?php if ($isLoggedIn): ?>
                    <span style="color: white; margin-right: 15px;">Привет, <?= htmlspecialchars($userLogin) ?></span>
                    <a href="/logout" style="text-decoration: none;">
                        <button>Log out</button>
                    </a>
                <?php else: ?>
                    <a href="/login" style="text-decoration: none;">
                        <button>Sign in</button>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
</div>
<div class="main-background">