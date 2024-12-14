<?php
session_start();

function checkAuth()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../views/auth/login.php");
        exit;
    }
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}