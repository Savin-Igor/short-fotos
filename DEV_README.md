#### Launching tools for static code analysis:

grumphp performs
php stan code checks analyzes the source code and
psalm tests runs the Psalm static analyzer with the configuration psalm.xml
php-cs-fixer corrects the code style using a configuration file.php-cs-fixer.php

```bash
./vendor/bin/grumphp run
./vendor/bin/phpstan analyse src tests
./vendor/bin/psalm --config=psalm.xml --no-cache
./vendor/bin/php-cs-fixer  fix  --config=.php-cs-fixer.php --allow-risky=yes
vendor/bin/rector process src --dry-run
```
