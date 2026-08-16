<?php

/**
 * Cancel Reasons Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Cancel Reasons
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCancelReasonRequest;
use App\Repositories\Repository;
use App\Models\CancelReason;
use App\Models\Cancel;
use App\Models\ScheduleCancel;
use App\Http\Start\Helpers;
use Validator;

class CancelReasonController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct(CancelReason $cancel_reason)
    {
        $this->helper = new Helpers;
        $this->model = new Repository($cancel_reason);
    }

    /**
     * Load Datatable for CancelReason
     *
     * @param array $dataTable  Instance of CancelReasonDataTable
     * @return datatable
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => trim((string) $request->get('status', '')),
            'cancelled_by' => trim((string) $request->get('cancelled_by', '')),
        ];

        $cancelReasons = CancelReason::query();

        if ($filters['search'] !== '') {
            $cancelReasons->where('reason', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['status'] !== '') {
            $cancelReasons->where('status', $filters['status']);
        }

        if ($filters['cancelled_by'] !== '') {
            $cancelReasons->where('cancelled_by', $filters['cancelled_by']);
        }

        $cancelReasons = $cancelReasons->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.cancel_reason.view', compact('cancelReasons', 'filters'));

    }

    /**
     * Add a New CancelReason
     *
     * @param array $request  Input values
     * @return redirect     to CancelReason view
     */
    public function add(StoreCancelReasonRequest $request)
    {
        if(!$_POST)
        {
            return view('admin.cancel_reason.add');
        }
        else if($request->submit)
        {
            $data = $request->all();
            $cancel_reason = $this->model->create($data);
            foreach($request->translations ?: [] as $translation_data) {  
                    $translation = $cancel_reason->getTranslationById(@$translation_data['locale'], $cancel_reason->id);
                    $translation->reason = $translation_data['reason'];                    
                    $translation->save();
            }

            $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function
            return redirect('admin/cancel-reason');
        }
        else
        {
            return redirect('admin/cancel-reason');
        }
    }

    /**
     * Update CancelReason Details
     *
     * @param array $request    Input values
     * @return redirect     to CancelReason View
     */
    public function update(StoreCancelReasonRequest $request)
    {
        if(!$_POST)
        {
			$data['result'] = $this->model->find($request->id);
            return view('admin.cancel_reason.edit', $data);
        }
        else if($request->submit)
        {
            $data = $request->all();
            $this->model->update($data,$request->id);
            $cancel_reason = $this->model->find($request->id);
          
            $removed_translations = explode(',', $request->removed_translations);
                foreach(array_values($removed_translations) as $id) {
                    $cancel_reason->deleteTranslationById($id);
                }   
                foreach($request->translations ?: [] as $translation_data) {  
                    $translation = $cancel_reason->getTranslationById(@$translation_data['locale'], $translation_data['id']);
                    $translation->reason = $translation_data['reason'];
                    $translation->save();
                }

            

            $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
            
            return redirect('admin/cancel-reason');
        }
        else
        {
            return redirect('admin/cancel-reason');
        }
    }

    /**
     * Delete CancelReason
     *
     * @param array $request    Input values
     * @return redirect     to CancelReason View
     */
    public function delete(Request $request)
    {
        if(Cancel::where('cancel_reason_id',$request->id)->exists() || ScheduleCancel::where('cancel_reason_id',$request->id)->exists()){
            $this->helper->flash_message('error','Sorry this cancel reason is already in use. So canont delete.');
            return back();
        }

        $active_cancel_reason = CancelReason::active()->count();
        $cancel_reason = $this->model->find($request->id);
        if($cancel_reason->status == 'Active' && $active_cancel_reason<=1){
            $this->helper->flash_message('error','Sorry, Minimum one Active cancel reason is required.');
            return back();
        }
        
        $this->model->delete($request->id);

        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function

        return redirect('admin/cancel-reason');
    }

}
