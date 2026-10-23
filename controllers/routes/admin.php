<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'admin':
            requireManager();
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $revenueStatistics = $controller->getRevenueStatistics();
            $quarterlyStatistics = $controller->getQuarterlyStatistics();
            include __DIR__ . "/../../view/home.php";
            break;
        case 'customers':
            requireManager();
            $customers = (new controller())->getCustomersWithLoyalty();
            include __DIR__ . "/../../view/customers.php";
            break;
        case 'home':
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $sanPhamBanChay = $controller->getBestSellingProducts();
            $sanPhamMoi = $controller->getNewestProducts();
            if (isManager()) {
                $revenueStatistics = $controller->getRevenueStatistics();
                $quarterlyStatistics = $controller->getQuarterlyStatistics();
            }
            include __DIR__ . "/../../view/home.php";
            break;
        default:
            $controller = new controller();
            $danhsach = $controller->hienthisanpham();
            $sanPhamBanChay = $controller->getBestSellingProducts();
            $sanPhamMoi = $controller->getNewestProducts();
            if (isManager()) {
                $revenueStatistics = $controller->getRevenueStatistics();
                $quarterlyStatistics = $controller->getQuarterlyStatistics();
            }
            include __DIR__ . "/../../view/home.php";
            break;
}
?>

