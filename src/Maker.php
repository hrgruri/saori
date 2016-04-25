<?php
namespace hrgruri\saori;

use hrgruri\saori\{ArticleInfo, Article};
use cebe\markdown\GithubMarkdown;

class Maker
{
    private $article_list;
    private $config;
    private $contents_path;
    public  $theme_config;
    public  $noapp;
    public  $tag_list;

    public function __construct($config, $article_list, $path, $tc, $tag_list)
    {
        $this->config        = $config;
        $this->article_list  = $article_list;
        $this->contents_path = $path;
        $this->theme_config  = $tc;
        $this->noapp         = $this->theme_config->noapp ?? 10;
        $this->tag_list      = $tag_list;
    }
    /**
     * @return string
     */
    public function getBlogTitle()
    {
        return (string)$this->config->title;
    }

    /**
     * @return null | string
     */
    public function getBlogDescription()
    {
        return isset($this->config->description) ? (string)$this->config->description : null;
    }

    /**
    * @param  string $name link name
    * @return null | string
    */
    public function getLink(string $name)
    {
        return isset($this->config->link->{$name}) ? (string)$this->config->link->{$name}: null;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return (string)$this->config->lang;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return (string)$this->config->author;
    }

    /**
     * @param  integer $count
     * @return array
     */
    public function getNewestArticle(int $count = 1)
    {
        $articles = [];
        $count  = $count > 0 ? $count : 1;
        $limit  = (count($this->article_list) < $count) ? count($this->article_list) : $count;
        $from   = strlen($this->contents_path);
        for ($i = 0; $i < $limit ; $i++) {
            $articles[] = new Article($this->article_list[$i]);
        }
        return $articles;
    }

    /**
     * @param  Article  $article
     * @param  integer  $number
     * @return null | array of Article
     */
    public function getNextArticle(Article $article, int $number = 1)
    {
        $id     = $article->getId();
        $result = null;
        $number  = $number > 0 ? $number : 1;
        $limit  = $id + $number < count($this->article_list) ? $id + $number + 1 : count($this->article_list);
        $from   = strlen($this->contents_path);
        for ($i = $id + 1; $i < $limit; $i++) {
            $result[] = new Article($this->article_list[$i]);
        }
        return $result;
    }

    /**
     * @param  string $path path of markdown file
     * @return string HTML
     */
    private function getHtml(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception("not found {$path}");
        }
        return (new GithubMarkdown)->parse(file_get_contents($path));
    }

    /**
     * @param  int    $page  corrent page number
     * @param  int    $number number of articles par page
     * @return bool
     */
    public function existsNextArticlePage(int $page, int $number)
    {
        if ($page < 0 || $number <= 0) {
            return false;
        }
        return (count($this->article_list) - $page * $number) > 0;
    }

    /**
     * @return int count articles
     */
    public function countArticle()
    {
        return count($this->article_list);
    }

    /**
     * @param  string   $name file name
     * @param  bool     $flag
     * @return null | string
     */
    public function loadMarkdown(string $name, bool $flag = true)
    {
        $html = null;
        if (file_exists("{$this->contents_path}/markdown/{$name}")) {
            $html = $this->getHtml("{$this->contents_path}/markdown/{$name}");
        }
        return $html;
    }

    public function getTagList()
    {
        return array_keys($this->tag_list);
    }


    /**
     * @param  string  $tag    tag name
     * @param  integer $number number of articles
     * @return array
     */
    public function getArticleByTag(string $tag, int $number = 1)
    {
        $number     = $number > 0 ? $number : 1;
        $articles   = [];
        if (isset($this->tag_list[$tag])) {
            $number = count($this->tag_list[$tag]) < $number ? count($this->tag_list[$tag]) : $number;
            for ($i = 0; $i < $number; $i++) {
                $articles[] = new Article($this->article_list[$this->tag_list[$tag][$i]]);
            }
        }
        return $articles;
    }
}