<?php
require_once __DIR__ . '/../models/khachhang.php';

function isCustomer(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'customer' && !empty($_SESSION['customer_id']);
}

function requireCustomer(): void
{
    if (!isCustomer()) {
        header('Location: index.php?act=login&redirect=checkout');
        exit;
    }
}

function customerId(): int
{
    return (int) ($_SESSION['customer_id'] ?? 0);
}

function customerLogin(array $customer): void
{
    session_regenerate_id(true);
    unset(
        $_SESSION['user_role'],
        $_SESSION['customer_id'],
        $_SESSION['customer_name'],
        $_SESSION['customer_email'],
        $_SESSION['customer_phone']
    );
    $_SESSION['user_role'] = 'customer';
    $_SESSION['customer_id'] = (int) $customer['id'];
    $_SESSION['customer_name'] = $customer['ho_ten'];
    $_SESSION['customer_email'] = $customer['email'] ?? '';
    $_SESSION['customer_phone'] = $customer['sdt'] ?? '';
}
