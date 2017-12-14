<?php
session_start();
if (!isset($_SESSION['username']))
    header("location: index.php");
require_once __DIR__.'/../src/classes/Account.php';
require_once __DIR__.'/../src/classes/User.php';
require_once __DIR__.'/../src/classes/DB.php';
require_once __DIR__.'/../src/DBconfig.php';
require_once __DIR__.'/../src/classes/ExchangeRates.php';
require_once __DIR__.'/../src/classes/Currency.php';

require_once __DIR__.'/../vendor/autoload.php';


$menubar = require_once __DIR__.'/menu.php';
$username = $_SESSION['username'];
$pageTitle = 'Счета';
$activePage = 'account';
if (isset($_GET['active'])) {
    $pageTitle = $menubar[$_GET['active']];
    $activePage = $_GET['active'];
}
$rates = ExchangeRates::getRatesFromApi();
$twigElems = array(
    'pageTitle' => $pageTitle,
    'username' => $username,
    'menubar' => $menubar,
    'exchangeRates' => $rates
);

switch ($activePage) {
    case 'account':
        $user_id = $_SESSION['user_id'];
        $accountsInfo = User::getAccountsInfo($user_id);
        $twigElems['accounts'] = $accountsInfo;

        $formAcition = 'create';
        if (isset($_GET['formAction']))
            $formAcition = $_GET['formAction'];
        $twigElems['formAction'] = $formAcition;
        $twigElems['currencies'] = Currency::getCurrencies();
        if ($formAcition == 'update') {
            $twigElems['acc_id'] = $_GET['acc_id'];
            $updAcc = array();
            foreach ($accountsInfo as $acc)
                if ($acc['id']==$_GET['acc_id']) {
                    $twigElems['acc_name'] = $acc['name'];
                    foreach ($acc['acc_curr'] as $accCurrency)
                        $updAcc[$accCurrency['curr_id']] = $accCurrency['init_value'];
                }
            $twigElems['updAcc'] = $updAcc;
        }
        break;
}
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader);
echo $twig->render($activePage.'.html', $twigElems);

//echo '<pre>'; print_r(Account::getBalance(1)); echo '</pre>';
