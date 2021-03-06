<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Expense;
use App\Profit;
use App\Website;
use App\AngelInvoice;
use App\FinancialReport;
use App\FinancialReportLossItem;
use App\FinancialReportProfitItem;
use App\Sanitizers\FinancialReportSanitizer;
use App\Validators\FinancialReportValidator;

class FinancialReportController extends Controller
{
    protected $data = [];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //Check page permission and redirect
        if( !Auth::user()->hasPagePermission('Sales & Loss') )
            return redirect('/webadmin');

        $this->data['currentSection'] = 'profit-loss';
        $this->prepareReports();
        
        return view('financial-reports.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['currentSection'] = 'profit-loss';
        $this->prepareProfitsAndExpenses();

        return view('financial-reports.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @return Response
     */
    public function store(Request $request)
    {
        // Sanitize
        $data = (new FinancialReportSanitizer)->sanitize($request->all());

        // Validate
        $validator = new FinancialReportValidator();
        if (! $validator->validate($data, 'create')) {
            return redirect()->back()->withInput($data)->withErrors($validator->getErrors());
        }

        $financialReport = FinancialReport::create($data);

        $this->saveProfits($financialReport, $data);
        $this->saveExpenses($financialReport, $data);

        Session::flash('message', 'Monthly report created successfully.');
        Session::flash('alert-class', 'alert-success');

        return redirect()->route('financial-reports.edit', [$financialReport->id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Website $website
     * 
     * @return Response
     */
    public function edit(FinancialReport $financialReport)
    {        
        $this->data['currentSection'] = 'profit-loss';
        $this->data['financialReport'] = $financialReport;
        $this->data['profits'] = $financialReport->profitItems()->orderBy('name')->get()->toArray();
        $this->data['expenses'] = $financialReport->expenseItems()->orderBy('name')->get()->toArray();

        return view('financial-reports.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $product
     * @return Response
     */
    public function update(FinancialReport $financialReport, Request $request)
    {
        // Sanitize
        $data = (new FinancialReportSanitizer)->sanitize($request->all());
        
        // Validate
        $validator = new FinancialReportValidator();
        if (! $validator->validate($data, 'update')) {
            return redirect()->back()->withInput($data)->withErrors($validator->getErrors());
        }

        $financialReport->fill($data);
        $financialReport->save();

        $this->saveProfits($financialReport, $data);
        $this->saveExpenses($financialReport, $data);

        Session::flash('message', 'Monthly report updated successfully.');
        Session::flash('alert-class', 'alert-success');

        return redirect()->route('financial-reports.edit', [$financialReport->id]);
    }

    protected function prepareProfitsAndExpenses()
    {
        $manualProfits = Profit::orderBy('name')->get();  
        $manualExpenses = Expense::orderBy('name')->get();
        $productFees = $this->getProductFees();

        $this->data['profits'] = [];
        $this->data['expenses'] = [];

        foreach ($productFees as $crmProductKey => $productFee) {
            $this->data['profits'][] = [
                'id' => uniqid(),
                'name' => $productFee['name'],
                'value' => $productFee['value']
            ];

            if (in_array($crmProductKey, AngelInvoice::expenseCrmProductKeys())) {
                $this->data['expenses'][] = [
                    'id' => uniqid(),
                    'name' => $productFee['name'],
                    'value' => $productFee['value']
                ];  
            }
        }

        foreach ($manualProfits as $manualProfit) {
            $this->data['profits'][] = [
                'id' => uniqid(),
                'name' => $manualProfit->name,
                'value' => $manualProfit->price
            ];
        }

        foreach ($manualExpenses as $manualExpense) {
            $this->data['expenses'][] = [
                'id' => uniqid(),
                'name' => $manualExpense->name,
                'value' => $manualExpense->price
            ];
        }

        usort($this->data['profits'], function($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });

        usort($this->data['expenses'], function($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });
    }

    protected function getProductFees()
    {            
        $productFees = [];

        array_map(function ($productName, $crmProduct) use (&$productFees){
            $productFees[$crmProduct] = [
                'name' => $productName,
                'value' => 0
            ];
        }, AngelInvoice::products(), array_keys(AngelInvoice::products()));

        foreach (Website::all() as $website) {
            foreach (AngelInvoice::crmProductKeys() as $crmProductKey) {
                $productValue = $website->getProductValue($crmProductKey);
                $productFees[$crmProductKey]['value'] += $productValue > 0 ? $productValue : 0;
            }
        }

        return $productFees;
    }

    protected function saveProfits(FinancialReport $financialReport, array $data)
    {
        $savedIds = [];
        foreach ($data['profits'] as $id => $profit) {
            $financialReportProfitItem = $financialReport->profitItems()->updateOrCreate([
                'id' => $id
            ], [
                'name' => $profit['name'],
                'value' => $profit['value']
            ]);

            $savedIds[] = $financialReportProfitItem->id;
        }
    }

    protected function saveExpenses(FinancialReport $financialReport, array $data)
    {
        $savedIds = [];
        foreach ($data['expenses'] as $id => $expense) {
            $financialReportExpenseItem = $financialReport->expenseItems()->updateOrCreate([
                'id' => $id
            ], [
                'name' => $expense['name'],
                'value' => $expense['value']
            ]);

            $savedIds[] = $financialReportExpenseItem->id;
        }
    }

    protected function prepareReports()
    {
        $this->data['profitNames'] = FinancialReportProfitItem::groupBy('name')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        $this->data['expenseNames'] = FinancialReportLossItem::groupBy('name')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        $financialReports = FinancialReport::orderByDesc('date')
            ->with('profitItems')
            ->with('expenseItems')
            ->get();

        $financialReports = $financialReports->map(function($financialReport) {
            $profitItemsArray = [];
            $expenseItemsArray = [];

            foreach ($financialReport->profitItems as $profitItem) {
                $profitItemsArray[$profitItem->name] = $profitItem->value;
            }

            foreach ($financialReport->expenseItems as $expenseItem) {
                $expenseItemsArray[$expenseItem->name] = $expenseItem->value;
            }

            $financialReport->profitItemsArray = $profitItemsArray;
            $financialReport->expenseItemsArray = $expenseItemsArray;

            return $financialReport;
        });

        $this->data['financialReports'] = $financialReports;
    }
}
