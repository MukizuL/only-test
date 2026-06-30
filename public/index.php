<?php

require "../src/auth.php";

if (isLoggedIn()) {
    header("Location: profile.php");
} else {
    header("Location: login.php");
}