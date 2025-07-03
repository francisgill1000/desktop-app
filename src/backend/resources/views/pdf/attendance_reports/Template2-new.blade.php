{{-- @php
phpinfo();
die();
@endphp --}}
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <style>
        .th-color {
            color: #0097a7 !important;
        }

        .th-font-size {
            font-size: 11px !important;
        }

        * {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 14px;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .pageCounter span {
            counter-increment: pageTotal;
        }

        #pageNumbers div:before {
            counter-increment: currentPage;
            content: "Page " counter(currentPage) " of ";
            font-size: 9px
        }

        #pageNumbers div:after {
            content: counter(pageTotal);
            font-size: 9px
        }

        #page-bottom-line {
            width: 100%;
            position: fixed;
            bottom: 15px;
            text-align: center;
            font-size: 12px;
            counter-reset: pageTotal;
            border-top: #a7a7a7 1px solid;
        }

        #page-header-line {
            width: 100%;
            /* position: fixed;
        top: 20px;
        left: 0;
        right: 0; */
            text-align: center;
            font-size: 12px;
            z-index: 1;
        }

        #footer .page:before {
            content: counter(page, decimal);
        }

        #footer .page:after {
            counter-increment: counter(page, decimal);
        }

        @page {
            margin: 15px 30px 0px 30px;
        }

        table {
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
        }

        tr:nth-child(even) {
            /* background-color: #eeeeee; */
            border: 1px solid #eeeeee;
        }

        th {
            font-size: 9px;

        }

        td {
            font-size: 9px;
            color: #535353 !important;
            padding: 3px;
        }

        footer {
            bottom: 0px;
            position: absolute;
            width: 100%;
        }

        .page-break {
            page-break-after: always;
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

        b {
            font-size: 12px
        }
    </style>
</head>

<body>

    <footer id="page-bottom-line">
        <table>
            <tr style="border :none">
                <td style="text-align: left;border :none;width:33%;padding:10px;">
                    Printed on : {{ date('d-M-Y ') }}
                </td>

                <td class="text-center" style="border :none;padding:10px">
                    Powered by {{ env('APP_NAME') }} &nbsp; <a style="font-size:9px;color:#0097a7 !important;"
                        href="https://mytime2cloud.com/"> https://mytime2cloud.com/</a>
                </td>
                <td style="text-align: right;border :none;padding:10px">
                    <div id="footer">
                        <div class="pageCounter">
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
                            <div style="font-size: 9px"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </footer>

    <div id="page-header-line">
        <table border="0" cellspacing="0" cellpadding="5">
            <tr>
                <td width="33%" style="border:none;">
                    <img src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                        alt="Company Logo" height="100">
                </td>
                <td width="34%" style="border:none;" class="text-center">
                    <b style="color: #005edf">MONTHLY ATTENDANCE REPORT</b> <br><br> <span>{{ $employee->full_name }}
                        ({{ $employee->employee_id ?? '---' }})</span>
                     <br><small style="font-size:12px;"> {{ date('M Y', strtotime($from_date)) }} -
                        {{ date('M Y', strtotime($to_date)) }}</small>
                </td>
                <td width="33%" style="font-size: 18px;  bold;text-align: right;border:none;">
                    <b>{{ $company->name ?? '' }}</b><br>
                    <small style="font-size:12px;">
                        {{ $company->user->email ?? '' }}<br>
                        {{ $company->contact->number ?? '' }}

                    </small>
                    <div style="font-size: 12px">
                        <small> {{ $company->location }}</small>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- <table style="margin-top: 30px !important;width:100% !important;">
        <tr>
            <td style="text-align: left; "><b>Full Name</b>: <span
                    style="font-size:10px;">{{ $employee->full_name }}</span></td>
        </tr>
    </table> --}}

    <table style="margin-top: 30px !important;width:100% !important;">
        <tr style="text-align: left; width:120px;background-color:#eeeeee !important;">
            <td style="text-align: left;"><b>Name</b>: <span
                    style="font-size:11px;">{{ $employee->display_name }}</span></td>
            <td style="text-align: left;"><b>EID</b>: <span style="font-size:11px;">{{ $employee->employee_id }}</span>
            </td>
            <td style="text-align: left;"><b>Dept</b>: <span
                    style="font-size:11px;">{{ $employee->department->name }}</span></td>
            <td style="text-align: left; width:120px;">

            </td>
            <td style="text-align: left;  padding:5px;color:green">
                <b>Present</b>: <span style="font-size:11px;">{{ $info->total_present ?? 0 }}</span>
            </td>
            <td style="text-align: left; color:red">
                <b>Absent</b>: <span style="font-size:11px;">{{ $info->total_absent ?? 0 }}</span>
            </td>
        </tr>
    </table>
    <table style="margin-top: 15px !important; margin-bottom:20px !important;width:100% !important; ">
        <tr>
            <tbody>
                <tr>
                    <td class="text-center" style="background-color:#fff !important;color: #005edf !important;">Dates
                    </td>
                    @foreach ($data as $date)
                        <td style="background-color:#fff !important;color: #005edf !important;" class="text-center">
                            {{ date('d', strtotime($date->date)) ?? '---' }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </tr>

        <tr>
            <td class="text-center" style="background-color:#fff !important;color: #005edf !important;"> Days </td>

            @foreach ($data as $date)
                <td class="text-center" style="background-color:#fff !important;color: #005edf !important;">
                    {{ date('D', strtotime($date->date)) ?? '---' }}
                </td>
            @endforeach
        </tr>

        <?php if (in_array($shift_type_id, [1, 4, 6])) { ?>
        <tr style="background-color: none;">
            <td class="text-center"> In </td>

            @foreach ($data as $date)
                <td class="text-center"> {{ $date->in ?? '---' }} </td>
            @endforeach
        </tr>
        <tr style="background-color: none;">
            <td class="text-center"> Out </td>
            @foreach ($data as $date)
                <td class="text-center"> {{ $date->out ?? '---' }} </td>
            @endforeach
        </tr>
        <?php } ?>

        @if ($shift_type_id == 2)
            <tr style="background-color: none;">
                <td class="text-center"> In1 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[0]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out1 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[0]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td class="text-center"> In2 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[1]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out2 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[1]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td class="text-center"> In3 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[2]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out3 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[2]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td class="text-center"> In4 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[3]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out4 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[3]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td class="text-center"> In5 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[4]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out5 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[4]['out'] ?? '---' }} </td>
                @endforeach
            </tr>
        @endif


        @if ($shift_type_id == 5)
            <tr style="background-color: none;">
                <td class="text-center"> In1 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[0]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out1 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[0]['out'] ?? '---' }} </td>
                @endforeach
            </tr>

            <tr style="background-color: none;">
                <td class="text-center"> In2 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[1]['in'] ?? '---' }} </td>
                @endforeach
            </tr>
            <tr style="background-color: none;">
                <td class="text-center"> Out2 </td>

                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->logs[1]['out'] ?? '---' }} </td>
                @endforeach
            </tr>
        @endif


        @if ($shift_type_id == 4 || $shift_type_id == 6)
            <tr>
                <td class="text-center"> Late In </td>
                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->late_coming ?? '---' }}
                    </td>
                @endforeach
            </tr>

            <tr>
                <td class="text-center"> Early Out </td>
                @foreach ($data as $date)
                    <td class="text-center"> {{ $date->early_going ?? '---' }}
                    </td>
                @endforeach
            </tr>
        @endif

        <tr>
            <td class="text-center" style="color: #005edf !important;"> Total Hrs </td>
            @foreach ($data as $date)
                <td class="text-center" style="color: #005edf !important;"> {{ $date->total_hrs ?? '---' }}
                </td>
            @endforeach
        </tr>

        <tr>
            <td class="text-center" style="color: #005edf !important;"> OT </td>
            @foreach ($data as $date)
                <td class="text-center" style="color: #005edf !important;"> {{ $date->ot ?? '---' }}
                </td>
            @endforeach
        </tr>
        <tr style="background-color:#eeeeee !important;">
            <td class="text-center"> Status </td>
            @foreach ($data as $date)
                @php
                    $statusColor = null;
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
                <td class="text-center" style="color:{{ $statusColor }} !important;"> {{ $date->status ?? '---' }}
                    <div style="font-size:6px">
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

    <table class="summary-table" style="width:100%;margin-top:40px">

        <tr class="summary-header"  style="border: none;background-color:#eeeeee">
            <th class="text-center" style="border :none; padding:5px">EID</th>
            <th class="text-center" style="border :none">Name</th>
            <th class="text-center" style="border :none">Department</th>
            <th class="text-center" style="border :none">Shift Type </th>
            <th class="text-center" style="border :none;color:#eeeeee;"> -----</th>

        </tr>
        <tr style="border: none">
            <td class="text-center" style="border :none; padding:5px;font-size:11px">
                {{ $employee->employee_id ?? '---' }}
            </td>
            <td class="text-center" style="border:none;font-size:11px">
                {{ $employee->full_name }}
            </td>
            <td class="text-center" style="border:none;font-size:11px">
                {{ $employee->department->name ?? '---' }}
            </td>
            <td class="text-center" style="border:none;font-size:11px">
                Multi In/Out
            </td>
        </tr>

        <tr class="summary-header" style="border: none;background-color:#eeeeee">
            <th class="text-center" style="border :none; padding:5px;">Present</th>
            <th class="text-center" style="border :none; padding:5px;">Absent</th>
            <th class="text-center" style="border :none; padding:5px;">Week Off</th>
            <th class="text-center" style="border :none; padding:5px;">Leaves</th>
            <th class="text-center" style="border :none; padding:5px;background-color:#eeeeee;color:#eeeeee">-----
            </th>
        </tr>
        <tr style="border: none">
            <td class="text-center" style="border :none; padding:5px;">
                {{ $info->total_present }} / {{ count($data) }}
            </td>
            <td class="text-center" style="border :none;">
                {{ $info->total_absent }} / {{ count($data) }}
            </td>

            <td class="text-center" style="border :none;">
                {{ $info->total_off }} / {{ count($data) }}
            </td>
            <td class="text-center" style="border :none;">
                {{ $info->total_leave }} / {{ count($data) }}
            </td>
        </tr>
        <tr class="summary-header" style="border: none;background-color:#eeeeee">
            <th class="text-center" style="border :none;">Holidays</th>
            <th class="text-center" style="border :none;">Missing</th>

            <th class="text-center" style="border :none; padding:5px;">Work Hours</th>
            <th class="text-center" style="border :none;">OT Hours</th>
            <th class="text-center" style="border :none;"> </th>
            {{-- <th style="text-align: center; border :none">Department</th> --}}
        </tr>
        <tr style="border: none">
            <td class="text-center" style="border :none;">
                {{ $info->total_holiday }} / {{ count($data) }}
            </td>
            <td class="text-center" style="border :none;">
                {{ $info->total_missing }} / {{ count($data) }}
            </td>
            <td class="text-center" style="border :none; padding:5px;">
                {{ $info->total_hours ?? 0 }}
            </td>
            <td class="text-center" style="border :none;">
                {{ $info->total_ot_hours ?? 0 }}
            </td>
            <td class="text-center" style="border :none;"> </td>
        </tr>

    </table>

    <table style="margin-top: 60px">
        <tr>
            <td style="border: none">
                <span style="color:green !important; font-size:10px; ">
                    P = Present,
                </span>
                <span style="color:red !important; font-size:10px; ">
                    A = Absent,
                </span>
                <span style="color:gray !important; font-size:10px; ">
                    W = Weekoff,
                </span>
                <span style="color:blue !important; font-size:10px; ">
                    L = Leaves,
                </span>
                <span style="color:pink !important; font-size:10px; ">
                    H = Holiday,
                </span>
                <span style="color:orange !important; font-size:10px; ">
                    M = Missing
                </span>

            </td>
        </tr>
    </table>
</body>

</html>
