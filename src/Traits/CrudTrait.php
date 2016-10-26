<?php namespace BadChoice\Grog\Traits;

use BadChoice\Grog\Services\ResourceRoute;
use Illuminate\Http\Request;
use App\Http\Requests;

trait CrudTrait{

    protected $viewPrefix = '';

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    |
    | Each model can provide its edit form config so the edit can be created
    | automatically
    |
    */
    public abstract function getFormConfig($object);

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
    public function getValidationRules($id = null){
        return [];
    }

    protected static function getNamespaceForModel($model){
        return ResourceRoute::modelClass($model);
    }

    //================================================================================================
    // CRUD Actions
    //================================================================================================
    /**
     * Show the list of the resources
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        $model  = ucfirst(str_singular( resourceName() ));
        $class  = $this->getModelClassFromRoute();
        return view( $this->viewPrefix . resourcePrefix() , ["data" => $class::all(), "model" => $model ]);
    }

    /**
     * Show the create view for the resource
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(){
        $class              = $this->getModelClassFromRoute();
        if(method_exists($class,'newDefault')) {
            $object = $class::newDefault(request()->input('parent_id'));
        }
        else{
            $object = new $class;
        }
        $formConfig         = $this->getFormConfig($object);
        $validationRules    = $this->getValidationRules();
        return view('admin.common.edit', compact('object', 'formConfig', 'validationRules') );
    }

    /**
     * Save a new resource to the database
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request){
        return $this->update($request, null);
    }

    /**
     * Show an specific resource
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id){
        $resource = str_singular( resourceName() );
        $viewPath = collect(explode('.',request()->route()->getName()))->slice(0,-2)->implode('.') . '.'.$resource;
        return view( $this->viewPrefix . $viewPath , ["object" => $this->getObjectFromRoute($id), "model" => ucfirst($resource) ]);
    }

    /**
     * Show the edit screen for a resource
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id){
        $object             = $this->getObjectFromRoute($id);
        $formConfig         = $this->getFormConfig($object);
        $validationRules    = $this->getValidationRules($id);
        return view('admin.common.edit', compact('object', 'formConfig', 'validationRules') );
    }

    /**
     * Update a resource
     *
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id){
        $this->validate($request, $this->getValidationRules($id) );
        try {
            if($id) {
                $object = $this->getObjectFromRoute($id);
                $object->update( setNullOnEmptyStrings( $request->all() ));
            }
            else{
                $class = $this->getModelClassFromRoute();
                $class::create( setNullOnEmptyStrings( $request->all() ));
            }
        }
        catch(\Exception $e){
            return redirect()->back()->withErrors(["update" => $e->getMessage()]);
        }
        return redirect()->back()->with(["message" => "Saved"]);
    }

    /**
     * Destroy a resource
     *
     * @param $id
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id){
        $object  = $this->getObjectFromRoute($id);
        // Check if object can be deleted (with custom conditions)
        if(method_exists($object,'canBeDeleted')) {
            try{
                if (!$object::canBeDeleted($id)) {
                    return redirect()->back()->withErrors(['delete' => trans('admin.cantDelete')]);
                }
            }catch(\Exception $e){
                return redirect()->back()->withErrors(['delete' => $e->getMessage()]);
            }
        }
        $object->delete();
        return request()->ajax() ? response()->json("ok",200) : redirect()->back()->with(["message" => "Deleted"]);
    }

    //====================================================================================================
    // Helpers
    //====================================================================================================
    protected function getModelClassFromRoute(){
        $model = ucfirst(str_singular( resourceName() ));
        return static::getNamespaceForModel($model);
    }

    protected function getObjectFromRoute($id){
        $class = $this->getModelClassFromRoute();
        return $class::find($id);
    }
}