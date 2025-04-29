<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h3 {
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payslip</h1>
        <p>For the period: {{ $payrollItem->payroll->start_date }} to {{ $payrollItem->payroll->end_date }}</p>
    </div>

    <div class="section">
        <h3>Employee Information</h3>
        <p>Name: {{ $payrollItem->employee->full_name }}</p>
        <p>Employee ID: {{ $payrollItem->employee->employee_id }}</p>
        <p>Department: {{ $payrollItem->employee->department->name ?? 'N/A' }}</p>
        <p>Position: {{ $payrollItem->employee->position->title ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <h3>Earnings</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td>{{ number_format($payrollItem->basic_salary, 2) }}</td>
                </tr>
                <tr>
                    <td>Allowances</td>
                    <td>{{ number_format($payrollItem->total_allowances, 2) }}</td>
                </tr>
                @foreach ($payrollItem->bonuses as $bonus)
                <tr>
                    <td>{{ $bonus->description }}</td>
                    <td>{{ number_format($bonus->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Deductions</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payrollItem->deductions as $deduction)
                <tr>
                    <td>{{ $deduction->description }}</td>
                    <td>{{ number_format($deduction->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Net Pay</h3>
        <p>{{ number_format($payrollItem->net_salary, 2) }}</p>
    </div>
</body>
</html>
