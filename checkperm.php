<?php

echo substr(sprintf('%o', fileperms('/var/www/masadb/data/.git')), -4);
