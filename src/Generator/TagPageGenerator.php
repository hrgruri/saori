<?php
namespace Hrgruri\Saori\Generator;

use Hrgruri\Saori\Article;
use Illuminate\Support\Collection;

class TagPageGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        $template   = $env->twig->loadTemplate('template/tags.twig');
        $html = $template->render(array(
            'maker'     =>  $env->maker
        ));
        self::putContents("{$env->paths['root']}/tag/index.html", $html);
        $template   = $env->twig->loadTemplate('template/articles.twig');
        foreach ($env->tag_list as $tag => $tag_ids) {
            for ($i = 1; count($tag_ids) > 0; $i++) {
                $articles   = [];
                $ids        = [];
                for ($j = 0; $j < $env->noapp; $j++) {
                    if (($id = array_shift($tag_ids)) === null) {
                        break;
                    }
                    $ids[] = $id;
                }
                foreach ($ids as $id) {
                    $articles[] = $env->articles[$id];
                }
                $html = $template->render(array(
                    'maker'     =>  $env->maker,
                    'articles'  =>  $articles,
                    'prev_page' =>  ($i != 1)               ? "/tag/{$tag}/".(string)($i-1) : null,
                    'next_page' =>  (count($tag_ids) > 0)   ? "/tag/{$tag}/".(string)($i+1) : null
                ));
                if ($i === 1) {
                    self::putContents("{$env->paths['root']}/tag/{$tag}/index.html", $html);
                }
                self::putContents("{$env->paths['root']}/tag/{$tag}/{$i}/index.html", $html);
            }
        }
    }

    public static function getTagList(Collection $articles) : Collection
    {
        $tags = [];
        foreach ($articles as $article) {
            foreach ($article->tags as $tag) {
                $tags[$tag][] = $article->getId();
            }
        }
        ksort($tags, SORT_NATURAL);
        return Collection::make($tags);
    }
}
