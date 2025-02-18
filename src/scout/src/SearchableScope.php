<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Scout;

use Hyperf\Database\Model\Builder as EloquentBuilder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Scout\Event\ModelsFlushed;
use Hyperf\Scout\Event\ModelsImported;
use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;

class SearchableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(EloquentBuilder $builder, Model $model)
    {
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('searchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunk($chunk ?: config('scout.chunk.searchable', 500), function ($models) {
                $models->filter->shouldBeSearchable()->searchable();
                $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
                $dispatcher->dispatch(new ModelsImported($models));
            });
        });
        $builder->macro('unsearchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunk($chunk ?: config('scout.chunk.unsearchable', 500), function ($models) {
                $models->unsearchable();
                $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
                $dispatcher->dispatch(new ModelsFlushed($models));
            });
        });
    }
}
