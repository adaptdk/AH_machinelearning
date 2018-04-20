
## Convert cli

### Converts JSON files with articles

    php console.php convert "exported/*" > processed/tagged-articles-1.json 2> /dev/null
    
### Build the fasttext file from articles

    php console.php fasttext tagged-articles-1.json processed/train.txt

## FastText

fastText is a library for efficient learning of word representations and sentence classification.

https://github.com/facebookresearch/fastText/

## Export articles from drupal
 - install node_export

Create script with:

    #!/bin/bash
    # Run this script in Drupal root app directory!
    # Requirements: drush command tool installed with ne-export command (you need Node Export module installed in Drupal)

    maxRows=500
    startFrom=0
    for i in {0..20}
    do
      startFrom=$(( (i)*maxRows ))
      echo "SELECT nid FROM node where node.type='magazine_article' limit $startFrom,$maxRows" # just for debugging
      php5.6 /usr/local/bin/drush ne-export  --file="/tmp/exported/articles-$i.json" --format='json' --sql="SELECT nid FROM node where node.type='magazine_article' limit $startFrom,$maxRows"
    done
    exit 0


### Set group concat length to avoid truncated data
    SET group_concat_max_len = 10000;

### Export tags from drupal
    SELECT CONCAT('[',GROUP_CONCAT(json_object(tid,name)),']') 
    FROM taxonomy_term_data WHERE vid = 16;

### Export themes from drupal
    SELECT CONCAT('[',GROUP_CONCAT(json_object(tid,name)),']') 
    FROM taxonomy_term_data WHERE vid = 99;