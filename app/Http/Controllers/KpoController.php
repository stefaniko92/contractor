<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KpoController extends Controller
{
    public function download(Request $request, $year)
    {
        $user = Auth::user();

        // Get all invoices for the specified year with items
        $invoices = Invoice::with(['client', 'items'])
            ->where('user_id', $user->id)
            ->whereYear('issue_date', $year)
            ->whereNotNull('issue_date')
            ->orderBy('issue_date')
            ->orderBy('invoice_number')
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', __('kpo.no_invoices'));
        }

        // Get company information (eager load to avoid N+1)
        $company = $user->userCompany()->first();
        $owner = null;

        if ($company) {
            $owner = $company->companyOwner()->first();
        }

        // Calculate totals
        $totalAmount = $invoices->sum('amount');

        // Generate PDF
        $pdf = Pdf::loadView('pdf.kpo-book', [
            'invoices' => $invoices,
            'year' => $year,
            'company' => $company,
            'owner' => $owner,
            'user' => $user,
            'totalAmount' => $totalAmount,
        ]);

        // Set PDF options
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);

        // Download the PDF
        $filename = 'KPO-Knjiga-' . $year . '.pdf';

        return $pdf->download($filename);
    }
}
