{{-- @php
phpinfo();
die();
@endphp --}}
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
            text-align: left;
        }

        tr:nth-child(even) {
            /* background-color: #eeeeee; */
            border: 1px solid #eeeeee;
        }

        th {
            font-size: 9px;

        }

        td {
            font-size: 7px;
        }

        footer {
            bottom: 0px;
            position: absolute;
            width: 100%;
        }

        /* .page-break {
            page-break-after: always;
        } */

        .main-table {
            padding-bottom: 20px;
            padding-top: 10px;
            padding-right: 15px;
            padding-left: 15px;
        }

        hr {
            position: relative;
            border: none;
            height: 2px;
            background: #c5c2c2;
            padding: 0px
        }

        .title-font {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 14px;
            font-weight: bold
        }

        .summary-header th {
            font-size: 10px
        }

        .summary-table td {
            font-size: 9px
        }
    </style>
</head>

<body>
    <div id="footer">
        <div class="pageCounter">
            {{-- <p class="page"> </p> --}}
            <p></p>
            @php
                $p = count($data);
                if ($p <= 1) {
                    echo '<span></span>';
                } else {
                    for ($a = 1; $a <= $p; $a++) {
                        echo '<span></span>';
                    }
            } @endphp
        </div>
        <div id="pageNumbers">
            <div class="page-number" style="font-size: 9px"></div>
        </div>
    </div>
    <footer id="page-bottom-line" style="padding-top: 100px!important">
        <hr style="width: 100%;">
        <table class="footer-main-table">
            <tr style="border :none">
                <td style="text-align: left;border :none"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                <td style="text-align: left;border :none"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;border :none"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2
                </td>
                <td style="text-align: right;border :none;">
                    <b>Powered by</b>: <span style="color:blue">
                        <a href="{{ env('APP_URL') }}" target="_blank">{{ env('APP_NAME') }}</a>
                    </span>
                </td>
                <td style="text-align: right;border :none">
                    Printed on : {{ date('d-M-Y ') }}
                </td>
            </tr>
        </table>
    </footer>
    <table style="margin-top: -20px !important;backgroundd-color:blue;padding-bottom:0px ">
        <tr>
            <td style="text-align: right;width: 300px; border :none; backgrounsd-color: red">




                <div class="col-12" style="background-coldor: rgb(253, 246, 246);padding:0px;margin:0px 5px">
                    <table style="padding:0px;margin:0px">
                        <tr style="text-align: left; border :none; padding:100px 0px;">
                            <td style="text-align: left; border :none;font-size:12px;padding:0 0 5px 0px;">
                                <b style="padding:0px;margin:0px">
                                    {{ $company->name }}
                                </b>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px;">
                                <span style="margin-left: 3px">P.O.Box
                                    {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px">
                                <span style="margin-left: 3px">{{ $company->location }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px">
                                <span style="margin-left: 3px">{{ $company->contact->number ?? '' }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:7px 0px">
                                <span style="margin-left: 3px">{{ '' }}</span>
                                <br>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <td style="text-align: left;width: 333px; border :none; padding:15px; backgrozusnd-color:blue">
                <div>
                    <table style="text-align: left; border :none;  ">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span class="title-font">
                                    {{ $info->report_type }} Report
                                </span>
                                {{-- <hr style="width: 230px"> --}}
                            </td>
                        </tr>
                        {{-- <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span style="font-size: 11px">
                                    {{ date('d-M-Y', strtotime($company->start)) }} -
                                    {{ date('d-M-Y', strtotime($company->end)) }}
                                </span>
                                <hr style="width: 230px">
                            </td>
                        </tr> --}}
                    </table>
                </div>
            </td>
            <td
                style="text-align: right;vertical-align:top;width: 300px; border :none; padding:15px;   backgrozund-color: red">
                <div style=" height:85px;  ">
                    <img src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                        style=" width:100px;max-width:150px;margin: 0px 0px 0px 0px; ">
                </div>
            </td>

        </tr>
    </table>
    @if ($employee->full_name)
        <table style="margin-top: 5px !important;">
            <tr>
                <td style="text-align: left;"><b>Full Name</b>: {{ $employee->full_name }}</td>
            </tr>
        </table>
    @endif
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; width:120px;">
            <td style="text-align: left;"><b>Name</b>: {{ $employee->display_name ?? '---' }}</td>
            <td style="text-align: left;"><b>EID</b>: {{ $employee->employee_id ?? '---' }}</td>
            <td style="text-align: left;"><b>Dept</b>: {{ $employee->department->name ?? '---' }}</td>
            <td style="text-align: left; width:120px;">

            </td>
            <td style="text-align: left;  padding:5px;color:green">
                <b>Present</b>:{{ $info->total_present ?? 0 }}
            </td>
            <td style="text-align: left; color:red">
                <b>Absent</b>:{{ $info->total_absent ?? 0 }}
            </td>
        </tr>
    </table>
    <table style="margin-top: 5px !important; margin-bottom:20px !important; ">
        <tr style="background-colors: #A6A6A6;">
            <tbody>
                <tr style="text-align:  center">
                    <td><b>Dates</b></td>
                    @foreach ($data as $date)
                        @php
                        @endphp
                        <td style="text-align:  center;">
                            {{ date('d', strtotime($date->date)) ?? '---' }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>

            @foreach ($data as $date)
                <td style="text-align:center;">
                    {{ date('D', strtotime($date->date)) ?? '---' }}
                </td>
            @endforeach
        </tr>

        <?php if (in_array($shift_type_id, [1, 4, 6])) { ?>
        <tr style="background-color: none;">
            <td> <b>In</b> </td>

            @foreach ($data as $date)
                <td style="text-align:  center;"> {{ $date->in ?? '---' }} </td>
            @endforeach
        </tr>
        <tr style="background-color: none;">
            <td> <b>Out</b> </td>
            @foreach ($data as $date)
                <td style="text-align:  center;"> {{ $date->out ?? '---' }} </td>
            @endforeach
        </tr>
        <?php } ?>

        @if ($shift_type_id == 2)
            <tr style="background-color: none;">
                <td> <b>In1</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[0]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out1</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[0]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td> <b>In2</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[1]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out2</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[1]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td> <b>In3</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[2]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out3</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[2]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td> <b>In4</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[3]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out4</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[3]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td> <b>In5</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[4]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out5</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[4]['out'] ?? '---' }} </td>
                @endforeach
            </tr>
        @endif


        @if ($shift_type_id == 5)
            <tr style="background-color: none;">
                <td> <b>In1</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[0]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out1</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[0]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td> <b>In2</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[1]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td> <b>Out2</b> </td>

                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->logs[1]['out'] ?? '---' }} </td>
                @endforeach
            </tr>
        @endif


        @if ($shift_type_id == 4 || $shift_type_id == 6)
            <tr>
                <td> <b>Late In</b> </td>
                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->late_coming ?? '---' }}
                    </td>
                @endforeach
            </tr>

            <tr>
                <td> <b>Early Out</b> </td>
                @foreach ($data as $date)
                    <td style="text-align:  center;"> {{ $date->early_going ?? '---' }}
                    </td>
                @endforeach
            </tr>
        @endif

        <tr>
            <td> <b>Total Hrs</b> </td>
            @foreach ($data as $date)
                <td style="text-align:  center;"> {{ $date->total_hrs ?? '---' }}
                </td>
            @endforeach
        </tr>

        <tr>
            <td> <b>OT</b> </td>
            @foreach ($data as $date)
                <td style="text-align:  center;"> {{ $date->ot ?? '---' }}
                </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>
            @foreach ($data as $date)
                @php
                    if ($date->status == 'P') {
                        $statusColor = 'green';
                    } elseif ($date->status == 'A') {
                        $statusColor = 'red';
                    } elseif ($date->status == 'M') {
                        $statusColor = 'orange';
                    } elseif ($date->status == 'O') {
                        $statusColor = 'gray';
                    } elseif ($date->status == 'L') {
                        $statusColor = 'blue';
                    } elseif ($date->status == 'H') {
                        $statusColor = 'pink';
                    } elseif ($date->status == '---') {
                        $statusColor = '';
                    }

                @endphp
                <td style="text-align:  center; color:{{ $statusColor }}"> {{ $date->status ?? '---' }}

                    <div class="secondary-value" style="font-size:6px">
                        @if ($date['shift'] && $date->status == 'P')
                            @php

                             

                                $shiftWorkingHours = $date['shift']['working_hours'];
                                $employeeHours = $date['total_hrs'];

                                if (
                                    $shiftWorkingHours !== '' &&
                                    $employeeHours !== '' &&
                                    $shiftWorkingHours !== '---' &&
                                    $employeeHours !== '---'
                                ) {
                                    [$hours, $minutes] = explode(':', $shiftWorkingHours);
                                    $shiftWorkingHours = $hours * 60 + $minutes;

                                    [$hours, $minutes] = explode(':', $employeeHours);
                                    $employeeHours = $hours * 60 + $minutes;

                                    if ($employeeHours < $shiftWorkingHours) {
                                        echo 'Short Shift';
                                    }
                            } @endphp
                        @endif
                    </div>
                </td>
            @endforeach
        </tr>
    </table>
</body>

</html>
