<?php
/**
 * Reset de Sessão — limpa preços salvos para forçar novos defaults
 */
session_start();
session_destroy();
header('Location: home.php');
exit;
