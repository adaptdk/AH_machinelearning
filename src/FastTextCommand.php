<?php namespace Console;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FastTextCommand extends SymfonyCommand
{

    private $stopWords = [
      'og','i','at','for','af','er','til','en','på',
      'med','det','de','der','som','har','den','et','om',
      'ikke','kan','var','fra','skal','være','vi',
      'ved','eller','sig','men','man','the','så','blev',
      'jeg','hun','of','deres','to','også','hvor','når',
      'and','kunne','over','efter','havde','ud','vil','have',
      'mere','bliver','han','alle','mange','andre','få',
      'meget','in','år','hvis','aspx','hvad','sider','flere',
      'sy','får','selv','siger','pct','hos','hvordan','se',
      'end','op','denne','dem','nogle','dette','eks','sin',
      'været','mellem','derfor','blive','under','da','her'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('fasttext')
             ->setDescription('Build the fasttext file from articles')
             ->addArgument('files', InputArgument::REQUIRED, 'The files with articles')
             ->addArgument('out', InputArgument::REQUIRED, 'The output file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $input->getArgument('files');
        $out = $input->getArgument('out');
        $lines = [];
        foreach (glob($files) as $filename) {
          $articles = json_decode(file_get_contents($filename));
          foreach ($articles as $article) {
            $tags = '';
            if (is_array($article->tags)) {
              $tags = '__label__' . mb_strtolower(join(' __label__', $article->tags));
            }
            $body = str_replace(array("\r", "\n", '-'), ' ', $article->body);
            $body = str_replace(array(',', '!','.','"','?','”','(',')','/',"'",':','’'), '', $body);
            $lines[] =  $tags . ' ' . mb_strtolower($body);
          }
        }
        shuffle($lines);
        file_put_contents($out, join(PHP_EOL, $lines ));
        $output->writeln('FastText file generated');
    }

}
