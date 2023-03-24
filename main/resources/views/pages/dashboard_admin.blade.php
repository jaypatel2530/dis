@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<style>
    .bg-set{
        background: rgb(2,0,36);
        background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,0.6698879380853904) 0%, rgba(0,212,255,1) 100%);
        border: 1px solid aqua;
        border-radius: 5px;
    }
</style>
<div class="container-fluid">

  <!-- Page Heading -->
  <!--<div class="d-sm-flex align-items-center justify-content-between mb-4">-->
  <!--  <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>-->
    <!--<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>-->
  <!--</div>-->

    <div class="row bg-set">
        
        <div class="col-xl-4 col-md-4 mb-4 mt-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Active Members</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $active_users }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        <div class="col-xl-4 col-md-4 mb-4 mt-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                InActive Members</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactive_users }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>  
        <div class="col-xl-4 col-md-4 mb-4 mt-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Members</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactive_users + $active_users }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>  
        <div class="col-xl-12 col-md-12 mb-4 mt-4">
          <!-- Bar Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Membership</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="myBarChart"></canvas>
                                    </div>
                                    <hr>
                                    
                                </div>
                            </div>
        </div>
                
        <!--<div class="col-xl-8 col-md-6 mb-4 mt-4">-->
        <!--  <div class="card border-left-primary shadow h-100 py-2">-->
        <!--    <div class="card-body">-->
        <!--      <div class="row no-gutters align-items-center">-->
        <!--        <div class="col mr-2">-->
        <!--          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Transfer funds to following account to use {{ env('APP_NAME') }}</div>-->
        <!--          <table class="">-->
        <!--              <tbody>-->
        <!--                <tr>-->
        <!--                  <th scope="row">Company Name : </th>-->
        <!--                  <td>{{ env('APP_NAME') }}</td>-->
        <!--                </tr>-->
                        
        <!--                @if(isset($fund_banks))-->
        <!--                    @foreach($fund_banks as $key => $fund_bank)-->
        <!--                        <tr>-->
        <!--                          <th scope="row">@if($key == 0) Account Details :  @else  @endif</th>-->
        <!--                          <td> {{ $fund_bank->account_number }} / {{ $fund_bank->ifsc }} / ( {{ $fund_bank->transfer_types }} )</td>-->
        <!--                        </tr>-->
        <!--                    @endforeach-->
        <!--                @endif-->
        <!--              </tbody>-->
        <!--            </table>-->
        <!--        </div>-->
        <!--      </div>-->
        <!--    </div>-->
        <!--  </div>-->
        <!--</div>-->
    </div>
    
   
</div>
@endsection
@section('customjs')
<script>
// Bar Chart Example
var ctx = document.getElementById("myBarChart");
var myBarChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    datasets: [{
      label: "Single",
      backgroundColor: "#4e73df",
      hoverBackgroundColor: "#2e59d9",
      borderColor: "#4e73df",
      data: [{{ $userArr_single }}],
    },{
      label: "Family",
      backgroundColor: "#e74a3b",
      hoverBackgroundColor: "#2e59d9",
      borderColor: "#e74a3b",
      data: [{{ $userArr_family }}],
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'month'
        },
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 12
        },
        maxBarThickness: 25,
      }],
      yAxes: [{
        ticks: {
          min: 0,
          max: 100,
          maxTicksLimit: 10,
          padding: 5,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return '' + number_format(value);
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false
    },
    tooltips: {
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
        }
      }
    },
  }
});
</script>
@endsection