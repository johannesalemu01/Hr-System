```vue
<template>
  <AuthenticatedLayout title="Payslip" :description="`Payslip for ${payrollItem.employee.name}`">
    <div class="max-w-4xl mx-auto">
   
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Payslip</h2>
            <p class="text-gray-600">
              For the period: {{ formatDate(payrollItem.payroll_period.start_date) }} - {{ formatDate(payrollItem.payroll_period.end_date) }}
            </p>
          </div>
          <div class="flex space-x-2">
            <button 
              @click="printPayslip" 
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md flex items-center"
            >
              <PrinterIcon class="h-5 w-5 mr-2" />
              Print
            </button>
            <button 
              @click="downloadPdf" 
              class="px-4 py-2 bg-primary-600 text-white rounded-md flex items-center"
            >
              <DownloadIcon class="h-5 w-5 mr-2" />
              Download PDF
            </button>
          </div>
        </div>
        

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-b border-gray-200 pb-6">
          <div>
            <h3 class="text-lg font-semibold mb-2">Company Information</h3>
            <p class="text-gray-700">Your Company Name</p>
            <p class="text-gray-600">123 Company Street</p>
            <p class="text-gray-600">City, State, ZIP</p>
            <p class="text-gray-600">Phone: (123) 456-7890</p>
            <p class="text-gray-600">Email: hr@yourcompany.com</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Employee Information</h3>
            <p class="text-gray-700">{{ payrollItem.employee.name }}</p>
            <p class="text-gray-600">Employee ID: {{ payrollItem.employee.employee_id }}</p>
            <p class="text-gray-600">Department: {{ payrollItem.employee.department }}</p>
            <p class="text-gray-600">Position: {{ payrollItem.employee.position }}</p>
            <p class="text-gray-600">Join Date: {{ formatDate(payrollItem.employee.join_date) }}</p>
          </div>
        </div>
        

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <h3 class="text-lg font-semibold mb-2">Payment Information</h3>
            <div class="grid grid-cols-2 gap-2">
              <p class="text-gray-600">Payment Date:</p>
              <p class="text-gray-700">{{ formatDate(payrollItem.payroll_period.payment_date) }}</p>
              
              <p class="text-gray-600">Payment Method:</p>
              <p class="text-gray-700">Bank Transfer</p>
              
              <p class="text-gray-600">Bank Name:</p>
              <p class="text-gray-700">{{ payrollItem.employee.bank_name }}</p>
              
              <p class="text-gray-600">Account Number:</p>
              <p class="text-gray-700">{{ payrollItem.employee.bank_account }}</p>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Work Summary</h3>
            <div class="grid grid-cols-2 gap-2">
              <p class="text-gray-600">Working Days:</p>
              <p class="text-gray-700">{{ payrollItem.working_days }} days</p>
              
              <p class="text-gray-600">Leave Days:</p>
              <p class="text-gray-700">{{ 22 - payrollItem.working_days }} days</p>
              
              <p class="text-gray-600">Overtime Hours:</p>
              <p class="text-gray-700">{{ getOvertimeHours() }} hours</p>
            </div>
          </div>
        </div>
        

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-green-600">Earnings</h3>
            <table class="w-full">
              <tbody>
                <tr class="border-b border-gray-200">
                  <td class="py-2 text-gray-600">Basic Salary</td>
                  <td class="py-2 text-right text-gray-700">{{ formatCurrency(payrollItem.earnings.basic_salary) }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                  <td class="py-2 text-gray-600">Allowances</td>
                  <td class="py-2 text-right text-gray-700">{{ formatCurrency(payrollItem.earnings.allowances) }}</td>
                </tr>
                <tr v-for="bonus in payrollItem.earnings.bonuses" :key="bonus.type" class="border-b border-gray-200">
                  <td class="py-2 text-gray-600">{{ bonus.description }}</td>
                  <td class="py-2 text-right text-gray-700">{{ formatCurrency(bonus.amount) }}</td>
                </tr>
                <tr class="font-semibold">
                  <td class="py-2 text-gray-800">Total Earnings</td>
                  <td class="py-2 text-right text-green-600">{{ formatCurrency(payrollItem.earnings.total_earnings) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2 text-red-600">Deductions</h3>
            <table class="w-full">
              <tbody>
                <tr v-for="deduction in payrollItem.deductions" :key="deduction.type" class="border-b border-gray-200">
                  <td class="py-2 text-gray-600">{{ deduction.description }}</td>
                  <td class="py-2 text-right text-gray-700">{{ formatCurrency(deduction.amount) }}</td>
                </tr>
                <tr class="font-semibold">
                  <td class="py-2 text-gray-800">Total Deductions</td>
                  <td class="py-2 text-right text-red-600">{{ formatCurrency(payrollItem.total_deductions) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        

        <div class="border-t border-gray-200 pt-4">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Net Pay</h3>
            <p class="text-xl font-bold" :class="payrollItem.net_pay < 0 ? 'text-red-600' : 'text-green-600'">
              {{ formatCurrency(payrollItem.net_pay) }}
            </p>
          </div>
          <p class="text-gray-600 mt-2">{{ amountInWords(payrollItem.net_pay) }}</p>
        </div>
      </div>
      

      <div class="flex justify-center mb-6">
        <Link 
          :href="route('payroll.index')" 
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md flex items-center"
        >
          <ArrowLeftIcon class="h-5 w-5 mr-2" />
          Back to Payroll
        </Link>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { 
  PrinterIcon, 
  DownloadIcon, 
  ArrowLeftIcon 
} from '@heroicons/vue/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  payrollItem: {
    type: Object,
    required: true
  }
});

// Format date
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-ET', {
    style: 'currency',
    currency: 'ETB',
    minimumFractionDigits: 2
  }).format(value);
};

// Get overtime hours
const getOvertimeHours = () => {
  // Calculate overtime hours based on bonuses
  const overtimeBonus = props.payrollItem.earnings.bonuses.find(bonus => bonus.type === 'overtime');
  if (overtimeBonus) {
    // Assuming overtime rate is 1.5x hourly rate
    const hourlyRate = props.payrollItem.earnings.basic_salary / (22 * 8); // 22 working days, 8 hours per day
    return Math.round((overtimeBonus.amount / (hourlyRate * 1.5)) * 10) / 10; // Round to 1 decimal place
  }
  return 0;
};

// Convert amount to words
const amountInWords = (amount) => {
  // This is a simplified version. In a real app, you'd use a library for this.
  const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
  const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
  
  const convertLessThanOneThousand = (num) => {
    if (num === 0) {
      return '';
    }
    
    let result = '';
    
    if (num <  10) {
      result = ones[num];
    } else if (num < 20) {
      result = teens[num - 10];
    } else if (num < 100) {
      result = tens[Math.floor(num / 10)] + (num % 10 !== 0 ? ' ' + ones[num % 10] : '');
    } else {
      result = ones[Math.floor(num / 100)] + ' Hundred' + (num % 100 !== 0 ? ' and ' + convertLessThanOneThousand(num % 100) : '');
    }
    
    return result;
  };
  
  if (amount === 0) {
    return 'Zero birr Only';
  }
  
  // Handle negative amounts
  const isNegative = amount < 0;
  amount = Math.abs(amount);
  
  // Split into birrs and paise
  const birrs = Math.floor(amount);
  const paise = Math.round((amount - birrs) * 100);
  
  let result = '';
  
  if (birrs > 0) {
    if (birrs < 1000) {
      result = convertLessThanOneThousand(birrs);
    } else if (birrs < 100000) {
      result = convertLessThanOneThousand(Math.floor(birrs / 1000)) + ' Thousand';
      if (birrs % 1000 !== 0) {
        result += ' ' + convertLessThanOneThousand(birrs % 1000);
      }
     else {
      result = convertLessThanOneThousand(Math.floor(birrs / 100000)) + ' Lakh';
      if (birrs % 100000 !== 0) {
        if (birrs % 100000 < 1000) {
          result += ' and ' + convertLessThanOneThousand(birrs % 100000);
        }
        else {
          result += ' ' + convertLessThanOneThousand(Math.floor((birrs % 100000) / 1000)) + ' Thousand';
          if (birrs % 1000 !== 0) {
            result += ' ' + convertLessThanOneThousand(birrs % 1000);
          }
        }
      }
    }
    
    result += ' birrs';
  }
  
  if (paise > 0) {
    result += (birrs > 0 ? ' and ' : '') + convertLessThanOneThousand(paise) + ' Paise';
  }
  
  result += ' Only';
  
  return (isNegative ? 'Negative ' : '') + result;
};

// Print payslip
const printPayslip = () => {
  window.print();
};

// Download PDF
const downloadPdf = () => {
  // In a real app, you'd use a library like jsPDF or make a server request
  alert('This would download the payslip as a PDF.');
};
};
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .bg-white, .bg-white * {
    visibility: visible;
  }
  .bg-white {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  button {
    display: none !important;
  }
}
</style>