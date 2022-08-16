<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetupRequest;
use App\Setup\Helper\PermissionsHelper;
use App\Setup\Helper\Requirements;
use App\Setup\Manager\ConfigManager;
use App\Setup\Manager\DatabaseManager;
use App\Setup\Manager\EnvironmentManager;
use App\Setup\Manager\FinalInstallManager;
use App\Setup\Manager\PurchaseCodeManager;
use App\Setup\Manager\StorageManager;
use App\Setup\Manager\UserManager;
use App\Setup\Validator\PurchaseCodeValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnvironmentController extends Controller
{

    protected $requirement;
    protected $permission;
    protected $environment;
    protected $purchaseCodeValidator;
    protected $databaseManager;
    protected $purchaseCodeManager;
    protected $manager;
    protected $storage;
    protected $userManager;
    protected $configManager;

    public function __construct(
        Requirements $requirements,
        PermissionsHelper $permission,
        EnvironmentManager $environment,
        PurchaseCodeValidator $codeValidator,
        DatabaseManager $databaseManager,
        PurchaseCodeManager $codeManager,
        FinalInstallManager $manager,
        StorageManager $storage,
        UserManager $userManager,
        ConfigManager $configManager
    )
    {
        $this->requirement = $requirements;
        $this->permission = $permission;
        $this->environment = $environment;
        $this->purchaseCodeValidator = $codeValidator;
        $this->databaseManager = $databaseManager;
        $this->purchaseCodeManager = $codeManager;
        $this->manager = $manager;
        $this->storage = $storage;
        $this->userManager = $userManager;
        $this->configManager = $configManager;

        parent::__construct();
    }

    public function index()
    {
        if ($this->requirement->isSupported() && $this->permission->isSupported()) {
            return view('setup.database');
        }

        throw new Exception(__('default.required_permission_and_server_requirements_is_complete'));
    }

    public function saveEnvironment(SetupRequest $request)
    {
        if ($this->purchaseCodeValidator->validate($request->code)) {

            $this->databaseManager->setConfig();

            $this->environment->saveFileWizard($request);

            return response()->json(['status' => true]);
        }

        throw ValidationException::withMessages([
            'code' => trans('default.invalid_purchase_code')
        ]);
    }

    public function admin()
    {
        return view('setup.admin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'email' => 'required|email',
            'password' => 'required', 'min:6',
        ]);

        throw_if(
            !env('PURCHASED_CODE'),
            ValidationException::withMessages([
                'code' => trans('default.invalid_purchase_code')
            ])
        );

        $this->manager->clear();

        $this->databaseManager->migrate();

        $this->userManager->create($request);

        $this->databaseManager->seed();

        $this->manager->finish();

        $this->storage->link();

        $this->configManager->set();

        return response()->json(['status' => true, 'message' => trans('default.app_installed_successfully')]);
    }
}
