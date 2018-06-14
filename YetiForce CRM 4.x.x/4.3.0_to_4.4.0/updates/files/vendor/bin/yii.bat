@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../yetiforce/yii2/yii
php "%BIN_TARGET%" %*
