# Convert one dump to another

If you have a massive file with PHP packages that looks like this: 

```csv
name;type;reqs;downloads
php-translation/loco-adapter;library;["symfony/yaml: ^2.7 || ^3.0 || ^4.0", "symfony/translation: ^2.7 || ^3.0 || ^4.0"];229856
```

... then we parse the file and filter the packages with the following criteria:

- At least one dependency to a Symfony component 
- It is a "library" or a "symfony-bundle"
- It has a few downloads


The output file looks like this: 

```csv
Package;Link;Downloads
php-translation/loco-adapter;library;229856
```

## How to run

```
composer update
./run.php data/input.csv data/output.csv
```