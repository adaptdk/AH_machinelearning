<?php namespace Console;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Html2Text\Html2Text;

class ConvertCommand extends SymfonyCommand
{

    public function __construct()
    {
        parent::__construct();
        $this->baseurl = 'https://dsr.dk';
        
        // Load tags from JSON
        $_tags = json_decode(file_get_contents('taxonomy/tags.json'), true);
        foreach ($_tags as $value) {
          $id = array_keys($value)[0];
          $this->tags[$id] = $value[$id]; 
        }

        // Load themes from JSON
        $_themes = json_decode(file_get_contents('taxonomy/themes.json'), true);
        foreach ($_themes as $value) {
          $id = array_keys($value)[0];
          $this->themes[$id] = $value[$id]; 
        }
    }

    public function configure()
    {
        $this->setName('convert')
             ->setDescription('Converts JSON files with articles')
             ->addArgument('files', InputArgument::REQUIRED, 'The files to be converted');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        $out = [];
        $files = $input->getArgument('files');
        foreach (glob($files) as $filename) {
          $articles = $this->convert($filename, $output);
          $count += sizeof($articles);
          $out = array_merge($out, $articles);
        }
        //$output->writeln($count);
        $output->writeln(json_encode($out));
    }

    protected function convert($filename, OutputInterface $output)
    {
        
        $raw = file_get_contents($filename);
        $articles = json_decode($raw);
        
        $out = [];

        foreach ($articles as $article) {
          
          // Convert to plain text
          $body = Html2Text::convert($article->field_body->und[0]->value);

          // Filter out media tags fom body
          $body = preg_replace('/\[\[.*?\]\]/s', '', $body);
          
          // Make URL's absolute
          $body = preg_replace('/(\/node\/\d+)/s', $this->baseurl . '${1}', $body);
          
          // Summary
          if (isset($article->field_teaser->und)) {
            $summary = $article->field_teaser->und[0]->value;
          }
          
          // Lookup tags
          $tags = [];
          if (isset($article->field_tags->und)) {
            $tags = array_map(
                function($o) { return $this->tags[$o->target_id]; }, 
                $article->field_tags->und
            );
          }

          // Lookup themes
          $themes = [];
          if (isset($article->field_article_theme->und)) {
            $themes = array_map(
              function($o) { return $this->themes[$o->target_id]; },
              $article->field_article_theme->und)
            ;
          }

          // URL to article
          $url = $this->baseurl . '/' . $article->path->source;
          
          $mergedTags = array_unique(array_merge($tags, $themes));

          if (sizeof($mergedTags) > 0) {
              $out[] = [ 
                'id' => $article->nid,
                'url' => $url,
                'title' => trim($article->title_field->da[0]->value),
                'summary' => trim($summary),
                'body' => trim($body),
                'tags' => $mergedTags,
              ];
          }
        }
        return $out;
    }

}
