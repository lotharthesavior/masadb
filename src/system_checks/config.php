<?php

return function () {
    return !file_exists(__DIR__ . "/../../config.json");
};
