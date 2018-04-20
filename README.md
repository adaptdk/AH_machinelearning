# Adapt Hackathon - machine learning

The goal of the project is to build an API that can tag articles based on machine learning. In addition you could build an UI where the editor can write a new article and see tags appear on-the-fly.

There is a large body of tagged articles prepared from the sygeplejesken magazine. The raw exported articles are located in the `exported` folder. In `processed` the file `tagged-articles.json` contains all articles with at least one tag and stripped for all the Drupal related data. The `train.txt` file contains the article body and the tags in a format prepared for fastText.

There's a lot that can be done to improve the model. Read the guide https://fasttext.cc/docs/en/supervised-tutorial.html for examples on how to optimize.

Example optimizations:

- Optimize the options to the learning command
- The title and summary is not part of the dataset
- there's a lot of links in the dataset
- there's no stopword filtering

## Convert cli

### Converts JSON files with articles

    php console.php convert "exported/*" > processed/tagged-articles.json 2> /dev/null

### Build the fasttext file from articles

    php console.php fasttext tagged-articles.json processed/train.txt

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
