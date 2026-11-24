<?php
    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    require_once("Models/pageModel.php");

    if ($uri === "/" || $uri === "/index.php" || $uri === "/accueil" || $uri === "/home") {
        $title = "Accueil";
        $template = "Views/Pages/pageAccueil.php";
    } else {
        http_response_code(404);
        $title = "Page introuvable";
        $template = "Views/Errors/http.php";
    }

    if (isset($template)) {
        require_once("Views/base.php");
    }
