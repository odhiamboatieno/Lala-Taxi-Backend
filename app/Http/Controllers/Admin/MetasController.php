<?php

/**
 * Metas Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Metas
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Metas;
use App\Http\Start\Helpers;
use Validator;
use Illuminate\Contracts\View\View;

class MetasController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $metas = Metas::query();

        if ($search !== '') {
            $metas->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        return view('admin.metas.view', [
            'metas' => $metas->orderByDesc('id')->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Update Meta Details
     *
     * @param array $request    Input values
     * @return redirect     to Metas View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
			$data['result'] = Metas::find($request->id);

            return view('admin.metas.edit', $data);
        }
        else if($request->submit)
        {
            // Edit Metas Validation Rules
            $rules = [
                    'title'    => 'required'
                    ];

            // Edit Metas Validation Custom Fields Name
            $niceNames = [
                        'title'    => 'Page Title'
                        ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $metas = Metas::find($request->id);

			    $metas->title        = $request->title;
			    $metas->description = $request->description;
			    $metas->keywords      = $request->keywords;

                $metas->save();

                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/metas');
            }
        }
        else
        {
            return redirect('admin/metas');
        }
    }

    /**
     * Delete Meta
     *
     * @param array $request    Input values
     * @return redirect     to Metas View
     */
    public function delete(Request $request)
    {
        Metas::find($request->id)->delete();

        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function

        return redirect('admin/metas');
    }
}
