<?php

namespace App\Nova;

use App\Constants\Statuses;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Waynestate\Nova\CKEditor;
use Whitecube\NovaFlexibleContent\Flexible;
use Emilianotisato\NovaTinyMCE\NovaTinyMCE;

class Page extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Page';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Контент';

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return "Статичные страницы";
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()
                ->sortable()
                ->hideFromIndex(),

            BelongsTo::make("Автор", "user", "App\Nova\User")
                ->withMeta([
                    'belongsToId' => $this->user_id ?? auth()->user()->id,
                    'extraAttributes' => [
                        'readonly' => true,
                    ]
                ])
                ->searchable()
                ->hideFromIndex(),

            Text::make("Заголовок", "title"),

            Text::make("Заголовок в шапке", "header_title")
                ->hideFromIndex(),

            Image::make("Изображение в заголовке", "main_image")
                ->hideFromIndex(),

            NovaTinyMCE::make("Краткое описание (год, страна)", "short_description")
                ->hideFromIndex()
                ->options([
                    'plugins' => [
                        'lists preview hr anchor pagebreak image wordcount fullscreen directionality paste textpattern table anchor code autolink link'
                    ],
                    'toolbar' => 'undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | image | bullist numlist outdent indent | table | code | anchor link  ',
//                    'use_lfm' => true,
//                    'lfm_url' => 'filemanager',
                ]),

            NovaTinyMCE::make("Вступление", "description_intro")
                ->hideFromIndex()
                ->options([
                    'plugins' => [
                        'lists preview hr anchor pagebreak image wordcount fullscreen directionality paste textpattern table anchor code autolink link'
                    ],
                    'toolbar' => 'undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | image | bullist numlist outdent indent | table | code | anchor link  ',
//                    'use_lfm' => true,
//                    'lfm_url' => 'filemanager',
                ]),

            Text::make("Ссылка", "slug")
                ->help("Генерируется автоматически если не заполнено. id+заголовок")
                ->hideFromIndex(),

//            CKEditor::make("Описание", "description")->showOnIndex()->limit(10),
//            NovaTinyMCE::make("Описание", "description")->options([
//                    'plugins' => [
//                        'lists source preview hr anchor pagebreak image wordcount fullscreen directionality paste textpattern'
//                    ],
//                    'toolbar' => 'undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | image | bullist numlist outdent indent | link',
//                    'use_lfm' => true
//                ])
//                ->showOnIndex()
//                ->limit(10),

            NovaTinyMCE::make("Описание", "description")
                ->hideFromIndex()
                ->options([
                    'plugins' => [
                        'lists preview hr anchor pagebreak image wordcount fullscreen directionality paste textpattern table anchor code autolink link'
                    ],
                    'toolbar' => 'undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | image | bullist numlist outdent indent | table | code | anchor link  ',
//                    'use_lfm' => true,
//                    'lfm_url' => 'filemanager',
                ]),

            Flexible::make("Слайдер", 'images')
                ->addLayout('image', 'image', [
                    Image::make('image'),
                    Text::make('url')
                ])
                ->hideFromIndex(),

            Select::make("Статус", "status")
                ->options(Statuses::get())
                ->displayUsingLabels(),

            new Panel("SEO", $this->seoPanel()),
        ];
    }

    protected function seoPanel()
    {
        return [
            Textarea::make("Seo Title"),
            Textarea::make("Seo Description"),
            Textarea::make("Seo Keywords"),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
