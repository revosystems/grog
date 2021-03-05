<?php

namespace BadChoice\Grog\Traits;

use BadChoice\Grog\Services\ResourceRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait CrudTrait
{
    protected $viewPrefix = '';
    protected $editPage   = 'admin.common.edit';

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    |
    | Each model can provide its edit form config so the edit can be created
    | automatically
    |
    */
    abstract public function getFormConfig($object);

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    |
    | This function will be called if exists when saving the model (new/update) so a validation is check against the request to make sure
    | All fields are OK.
    | Use the laravel default validation rules structure
    |
    | If the function doesn't exists for the model, no validation will be launched
    |
    */
    public function getValidationRules($id = null)
    {
        return [];
    }

    protected static function getNamespaceForModel($model)
    {
        return ResourceRoute::modelClass($model);
    }

    //================================================================================================
    // CRUD Actions
    //================================================================================================

    /**
     * Show the list of the resources
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $model  = ucfirst(Str::singular(resourceName()));
        $class  = $this->getModelClassFromRoute();
        return view($this->viewPrefix . resourcePrefix(), ["data" => $class::all(), "model" => $model ]);
    }

    /**
     * Show the create view for the resource
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $class              = $this->getModelClassFromRoute();
        if (method_exists($class, 'newDefault')) {
            $object = $class::newDefault(request()->input('parent_id'));
        } else {
            $object = new $class;
        }
        $formConfig         = $this->getFormConfig($object);
        $validationRules    = $this->getValidationRules();
        return view($this->editPage, compact('object', 'formConfig', 'validationRules'));
    }

    /**
     * Save a new resource to the database
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        return $this->update($request, null);
    }

    /**
     * Show an specific resource
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $resource = Str::singular(resourceName());
        $viewPath = collect(explode('/', request()->path()))->slice(0, -2)->implode('.') . '.'.$resource;
        return view($this->viewPrefix . $viewPath, ["object" => $this->getObjectFromRoute($id), "model" => ucfirst($resource) ]);
    }

    /**
     * Show the edit screen for a resource
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $object             = $this->getObjectFromRoute($id);
        $formConfig         = $this->getFormConfig($object);
        $validationRules    = $this->getValidationRules($id);
        return view($this->editPage, compact('object', 'formConfig', 'validationRules'));
    }

    /**
     * Update a resource
     *
     * @param Request $request
     * @param $id
     * @return CrudTrait|\Illuminate\Http\RedirectResponse|string
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->getValidationRules($id));
        try {
            $object = $this->updateOrCreateObject($id);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
        return $this->respondOk($object->id, "Saved");
    }

    /**
     * Destroy a resource
     *
     * @param $id
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $object  = $this->getObjectFromRoute($id);
        // Check if object can be deleted (with custom conditions)
        if (method_exists($object, 'canBeDeleted')) {
            try {
                if (! $object::canBeDeleted($id)) {
                    return $this->respondError(trans('admin.cantDelete'));
                }
            } catch (\Exception $e) {
                return $this->respondError($e->getMessage());
            }
        }
        $object->delete();
        return $this->respondOk("ok", "Deleted");
    }

    //====================================================================================================
    // Helpers
    //====================================================================================================
    protected function getModelClassFromRoute()
    {
        $model = ucfirst(Str::singular(resourceName()));
        return static::getNamespaceForModel($model);
    }

    protected function getObjectFromRoute($id)
    {
        $class = $this->getModelClassFromRoute();
        return $class::findOrFail($id);
    }

    protected function updateOrCreateObject($id)
    {
        if ($id) {
            $object = $this->getObjectFromRoute($id);
            $object->update(setNullOnEmptyStrings($request->all()));
        } else {
            $class  = $this->getModelClassFromRoute();
            $object = $class::create(setNullOnEmptyStrings($request->all()));
        }
        return $object;
    }

    protected function respondOk($data, $message = "Ok")
    {
        return request()->ajax() ? response()->json($data) : redirect()->back()->with(["message" => $message]);
    }

    protected function respondError($message, $code = 422)
    {
        return request()->ajax() ? response()->json($message, $code) : redirect()->back()->withErrors(['message' => $message]);
    }
}
