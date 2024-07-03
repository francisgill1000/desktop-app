<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<body>

    @php
        $data = $data->toArray();
    @endphp

    <div id="footer">
        <div class="pageCounter">
            <p></p>
            @if (count($data) > 1)
                @foreach (range(1, count($data)) as $_)
                    <span></span>
                @endforeach
            @else
                <span></span>
            @endif
        </div>
        <div id="pageNumbers">
            <div class="page-number" style="font-size: 9px"></div>
        </div>
    </div>
    <footer id="page-bottom-line" style="padding-top: 100px!important">
        <hr style="width: 100%;">
        <table class="footer-main-table">
            <tr style="border :none">
                <td style="text-align: left;border :none;">
                    <b>Powered by</b>: <span style="color:blue">
                        <a href="https://ideahrms.com/" target="_blank">ideahrms.com</a>
                    </span>
                </td>
                <td style="text-align: right;border :none">
                    Printed on : {{ date('d-M-Y ') }}
                </td>
            </tr>
        </table>
    </footer>
    @php
        $statusColor = '';
        $i = 0;
    @endphp

    <table class="main-table">


        <tr style="border:none;">
            <td style="width:22%;background:reds;text-align:center;border:none;">
                <div style="margin-top:40px;">
                    <img style="width:100%;"
                        src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0">

                    {{-- @if (env('APP_ENV') !== 'local')
                            <img src="{{ $company->logo }}">
                        @else
                            <img style="width:100%;"
                                src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0">
                        @endif --}}
                </div>
            </td>
            <td style="width:;border:none;">
                <div>
                    <table style="text-align: left; border :none;  ">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span class="title-font">
                                    Visitor {{ $info['frequency'] }} Report
                                </span>
                                <hr style="width: 230px">
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span style="font-size: 11px">
                                    {{ $info['company']['name'] }}
                                </span>
                                <hr style="width: 230px">
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width:22%;background:reds;border:none;">
                <table class="summary-table"
                    style="border-top: 1px #c5c2c2 solid;border-bottom: 1px #c5c2c2 solid;margin-top:20px">

                    <tr class="summary-header" style="background-color:#eeeeee">
                        <th style="text-align: center; border :none; padding:5px">Day</th>
                        <th style="text-align: center; border :none; padding:5px">Date</th>
                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border:none;font-size:11px">
                            {{ date('D', strtotime($info['daily_date'])) }}
                        </td>
                        <td style="text-align: center; border:none;font-size:11px">
                            {{ $info['daily_date'] }}
                        </td>
                    </tr>
                    <tr class="summary-header" style="background-color:#eeeeee">
                        <th style="text-align: center; border :none">Total Record</th>
                        <th style="text-align: center; border :none">Status</th>
                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border:none;font-size:11px">
                            <b> {{ count($data) }}</b>
                        </td>
                        <td style="text-align: center; border:none;font-size:11px">
                            <b>{{ $info['status'] }}</b>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <td colspan="3" style="border: none;padding-top:10px;">
            <table>
                <tr style="text-align: left;font-weight:bold;margin-top:20px;background:; width:100%;">
                    <td style="text-align: center;"> Visitor ID </td>
                    <td style="text-align: center;"> Name </td>

                    <td style="text-align: center;"> In </td>
                    <td style="text-align: center;"> Out </td>
                    <td style="text-align: center;"> Total Hours </td>
                    <td style="text-align: center;"> Status </td>
                    <td style="text-align: center;"> Device In </td>
                    <td style="text-align: center;"> Device Out </td>
                    <td style="text-align: center;"> Reason </td>
                </tr>
                <tbody>
                    @foreach ($data as $record)
                        <tr>
                            <td style="text-align:center;">{{ $record['visitor_id'] }}</td>
                            <td style="text-align:center;">{{ $record['visitor']['full_name'] }}</td>
                            <td style="text-align:center;"> {{ $record['in'] }} </td>
                            <td style="text-align:center;"> {{ $record['out'] }} </td>
                            <td style="text-align:center;"> {{ $record['total_hrs'] }} </td>
                            <td style="text-align:center;"> {{ $record['status'] }} </td>
                            <td style="text-align:center;"> {{ $record['device_in']['short_name'] }} </td>
                            <td style="text-align:center;"> {{ $record['device_out']['short_name'] }} </td>
                            <td style="text-align:center;">Reason</td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
        </td>
    </table>
</body>
<style>
    .my-break {
        page-break-after: always
    }

    .pageCounter span {
        counter-increment: pageTotal
    }

    #pageNumbers div:before {
        counter-increment: currentPage;
        content: "Page " counter(currentPage) " of "
    }

    #pageNumbers div:after {
        content: counter(pageTotal)
    }

    #footer {
        position: fixed;
        top: 720px;
        right: 0;
        bottom: 0;
        text-align: center;
        font-size: 12px
    }

    #page-bottom-line {
        position: fixed;
        right: 0;
        bottom: -6px;
        text-align: center;
        font-size: 12px;
        counter-reset: pageTotal
    }

    #footer .page:before {
        content: counter(page, decimal)
    }

    #footer .page:after {
        counter-increment: counter(page, decimal)
    }

    @page {
        margin: -10px 30px 25px 30px;
    }

    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        border: none;
        width: 100%
    }

    td,
    th {
        border: 1px solid #eee;
        text-align: left
    }

    tr:nth-child(even) {
        border: 1px solid #eee
    }

    th {
        font-size: 9px
    }

    td {
        font-size: 9px
    }

    footer {
        bottom: 0;
        position: absolute;
        width: 100%
    }

    .main-table {
        padding-bottom: 20px;
        padding-top: 10px;
        padding-right: 15px;
        padding-left: 15px
    }

    .footer-main-table {
        padding-bottom: 7px;
        padding-top: 0;
        padding-right: 15px;
        padding-left: 15px
    }

    .title-font {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px;
        font-weight: 700
    }

    .summary-header th {
        font-size: 10px
    }

    .summary-table td {
        font-size: 9px
    }
</style>

</html>
