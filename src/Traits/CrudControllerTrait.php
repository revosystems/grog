<?php

namespace BadChoice\Grog\Traits;

use Illuminate\Http\Request;

trait CrudControllerTrait
{
    protected $viewBaseFolder = '';

    /**
     * Updates a model or creates a new one if `id` is null
     *
     * @param $request Request dependency injection of the request for doing the validation
     * @param $model
     * @param null $id when null creates a new row
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, $model, $id = null)
    {
        $class = static::getNamespaceForModel($model).$model;

        $rules = null;
        if (method_exists($class, 'getValidationRules')) {
            $rules = $class::getValidationRules($id);
        }
        if ($rules) {
            $this->validate($request, $rules);
        }
        // Create if no id or update if there is ID
        try {
            if ($id == null) {
                $object = $class::create(setNullOnEmptyStrings(\Illuminate\Support\Facades\Request::all()));
            } else {
                $object = $class::find($id);
                $object->update(setNullOnEmptyStrings(\Illuminate\Support\Facades\Request::all()));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(["update" => $e->getMessage()]);
        }
        return redirect()->back()->with(["message" => "Saved"]);
    }

    public function delete(Request $request, $model, $id)
    {
        $class          = static::getNamespaceForModel($model).$model;
        $object         = $class::find($id);
        // Check if object can be deleted (with custom conditions)
        try {
            if (!$class::canBeDeleted($id)) {
                return redirect()->back()->withErrors(['delete' => trans('admin.cantDelete')]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['delete' => $e->getMessage()]);
        }

        $object->delete();

        if ($request->ajax()) {
            return response()->json("ok", 200);
        } else {
            return redirect()->back()->with(["message" => "Deleted"]);
        }
    }

    public function edit($model, $id)
    {
        $class          = static::getNamespaceForModel($model).$model;
        $object         = $class::find($id);

        $viewBaseFolder = $this->viewBaseFolder;
        if (method_exists($object, 'getEditFormConfig')) {
            $formConfig = $object->getEditFormConfig();
        } else {
            $formConfig = include base_path().'/resources/forms/edit/'.$model.'.php';
        }

        $validationRules = [];
        if (method_exists($object, 'getValidationRules')) {
            $validationRules = $class::getValidationRules();
        }

        return view('admin.common.edit', compact('object', 'viewBaseFolder', 'model', 'formConfig', 'validationRules'));
    }

    public function create($model, $parentId = null)
    {
        $class = static::getNamespaceForModel($model).$model;
        if (method_exists(new $class, 'newDefault')) {
            $object     = $class::newDefault($parentId);
        } else {
            $object     = new $class;
        }

        $viewBaseFolder = $this->viewBaseFolder;
        if (method_exists($object, 'getEditFormConfig')) {
            $formConfig = $object->getEditFormConfig();
        } else {
            $formConfig = include base_path().'/resources/forms/edit/'.$model.'.php';
        }

        $validationRules = [];
        if (method_exists($object, 'getValidationRules')) {
            $validationRules = $class::getValidationRules();
        }
        return view('admin.common.edit', compact('object', 'viewBaseFolder', 'model', 'formConfig', 'validationRules'));
    }

    protected static function getNamespaceForModel($model)
    {
        //I usually have a BaseModel that implements this function
        return "";
    }
}
