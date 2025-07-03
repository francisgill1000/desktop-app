<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

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
    @php
        $statusColor = '';
        $i = 0;
    @endphp

    <div style="margin-top:40px;">
        <hr>
        <table>
            <thead>
                <tr style="text-align: left;margin-top:20px;width:100%">
                    <td class="text-center th-color th-font-size" style="width:10px"> # </td>
                    <td class="text-center th-color th-font-size" colspan="2"> Date </td>
                    <td class="text-center th-color th-font-size" colspan="2"> Shift </td>

                    @if ($shift_type_id == 2)
                        @for ($i = 0; $i < 7; $i++)
                            <td class="text-center th-color th-font-size"> In{{ $i + 1 }} </td>
                            <td class="text-center th-color th-font-size"> Out{{ $i + 1 }}
                            </td>
                        @endfor
                    @else
                        <td class="text-center th-color th-font-size" colspan="4"> In Time </td>
                        <td class="text-center th-color th-font-size" colspan="4"> Out Time </td>
                        <td class="text-center th-color th-font-size" colspan="3"> Late In </td>
                        <td class="text-center th-color th-font-size" colspan="3"> Early Out </td>
                    @endif

                    <td class="text-center th-color th-font-size"> Total Hours </td>
                    <td class="text-center th-color th-font-size"> OT </td>
                    <td class="text-center th-color th-font-size"> Status </td>
                </tr>
            </thead>

            <tbody>
                @foreach ($data as $index => $date)
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
                            $statusColor = '#f34100ed';
                        }
                    @endphp

                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center" colspan="2">{{ $date->date ?? '---' }}
                            <div class="secondary-value" style="font-size:6px">{{ $date->day ?? '---' }}</div>
                        </td>
                        <td class="text-center" colspan="2">
                            @if ($date->status == 'O')
                                Week-Off
                            @else
                                @if ($date->schedule)
                                    {{ $date->schedule->shift->on_duty_time }} -
                                    {{ $date->schedule->shift->off_duty_time }}
                                    <div class="secondary-value" style="font-size:6px">
                                        {{ $date->schedule->shift->name }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        @if ($shift_type_id == 2)
                            @for ($i = 0; $i < 7; $i++)
                                <td class="text-center">
                                    {{ $date->logs[$i]['in'] ?? '---' }}
                                    <div class="secondary-value" style="font-size:6px">
                                        {{ $date->logs[$i]['device_in'] ?? '---' }}</div>
                                </td>
                                <td class="text-center">
                                    {{ $date->logs[$i]['out'] ?? '---' }}
                                    <div class="secondary-value" style="font-size:6px">
                                        {{ $date->logs[$i]['device_out'] ?? '---' }}</div>
                                </td>
                            @endfor
                        @else
                            <td class="text-center" colspan="4">{{ $date->in ?? '---' }}</td>
                            <td class="text-center" colspan="4">{{ $date->out ?? '---' }}</td>
                            <td class="text-center" colspan="3">{{ $date->late_coming ?? '---' }}</td>
                            <td class="text-center" colspan="3">{{ $date->early_going ?? '---' }}</td>
                        @endif

                        <td class="text-center">{{ $date->total_hrs ?? '---' }}</td>
                        <td class="text-center">{{ $date->ot ?? '---' }}</td>
                        <td class="text-center" style="color: {{ $statusColor }} !important ">
                            {{ str_replace('O', 'W', $date->status) ?? '---' }}
                            <div class="secondary-value" style="font-size:6px">
                                @if ($date['shift'] && $date->status == 'P')
                                    @php
                                        $shiftWorkingHours = $date['shift']['working_hours'];
                                        $dateHours = $date['total_hrs'];
                                        if (
                                            $shiftWorkingHours &&
                                            $dateHours &&
                                            $shiftWorkingHours !== '---' &&
                                            $dateHours !== '---'
                                        ) {
                                            [$h1, $m1] = explode(':', $shiftWorkingHours);
                                            $shiftMinutes = $h1 * 60 + $m1;

                                            [$h2, $m2] = explode(':', $dateHours);
                                            $workedMinutes = $h2 * 60 + $m2;

                                            if ($workedMinutes < $shiftMinutes) {
                                                echo 'Short Shift';
                                            }
                                        }
                                    @endphp
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Page break after every 20 records --}}
                    @if (($index + 1) % 20 === 0 && !$loop->last)
            </tbody>
        </table>
    </div>
    <div class="page-break"></div>

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
                    <br><small style="font-size:12px;"> {{ date('d M Y') }} -
                        {{ date('dM Y') }}</small>
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
    <div style="margin-top:40px">
        <hr>
        <table>
            <thead>
                <tr style="text-align: left;margin-top:20px;width:100%">
                    <td class="text-center th-color th-font-size" style="width:10px"> # </td>
                    <td class="text-center th-color th-font-size" colspan="2"> Date </td>
                    <td class="text-center th-color th-font-size" colspan="2"> Shift </td>

                    @if ($shift_type_id == 2)
                        @for ($i = 0; $i < 7; $i++)
                            <td class="text-center th-color th-font-size"> In{{ $i + 1 }} </td>
                            <td class="text-center th-color th-font-size"> Out{{ $i + 1 }} </td>
                        @endfor
                    @else
                        <td class="text-center th-color th-font-size" colspan="4"> In Time </td>
                        <td class="text-center th-color th-font-size" colspan="4"> Out Time </td>
                        <td class="text-center th-color th-font-size" colspan="3"> Late In </td>
                        <td class="text-center th-color th-font-size" colspan="3"> Early Out </td>
                    @endif

                    <td class="text-center th-color th-font-size"> Total Hours </td>
                    <td class="text-center th-color th-font-size"> OT </td>
                    <td class="text-center th-color th-font-size"> Status </td>
                </tr>

            </thead>
            <tbody>
                @endif
                @endforeach

                <tr>
                    @if ($shift_type_id !== 2)
                        <td class="text-center" colspan="13">
                        </td>
                        <td class="text-center" colspan="3">
                            {{ $info->late_coming ?? '---' }}
                        </td>
                        <td class="text-center" colspan="3">
                            {{ $info->early_going ?? '---' }}
                        </td>
                    @else
                        <td class="text-center" colspan="19"></td>
                    @endif

                    <td class="text-center" style="color: #0097a7 !important;">{{ $info->total_hours ?? 0 }}</td>
                    <td class="text-center" style="color: #0097a7 !important;"> {{ $info->total_ot_hours ?? 0 }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>


        <table class="summary-table" style="width:100%;margin-top:40px">

            <tr class="summary-header" style="border: none;background-color:#eeeeee">
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
    </div>

</body>
<style>
    .th-color {
        color: #0097a7 !important;
    }

    .th-font-size {
        font-size: 12px !important;
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
</style>

</html>
