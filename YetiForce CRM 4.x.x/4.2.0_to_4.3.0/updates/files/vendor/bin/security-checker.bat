@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sensiolabs/security-checker/security-checker
php "%BIN_TARGET%" %*
