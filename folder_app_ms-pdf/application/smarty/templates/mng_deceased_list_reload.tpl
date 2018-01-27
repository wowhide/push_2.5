<table id="deceased_list">
    <tr>
        <th class="deceased_name">故人様名</th><th class="deathday">命日</th><th class="issue_day">発注日時</th><th class="entry_day">発行日</th><th class="state">発行状態</th><th class="charge">担当者</th><th class="operate1"></th><th class="operate2"></th>
    </tr>
{foreach from = $deceasedInfoList item = "deceasedInfo"}
    
{if $deceasedInfo.issue_state_code == "1"}
    <tr class="issue_data">
{elseif $deceasedInfo.issue_state_code == "2"}
    <tr class="issued_data">
{elseif $deceasedInfo.issue_state_code == "4"}
    <tr class="del_data">
{else}
    <tr>
{/if}
        <td>{$deceasedInfo.deceased_name|escape}&nbsp;様</td>
        <td>{date('Y/m/d', strtotime($deceasedInfo.deceased_deathday))}</td>
        <td>{$deceasedInfo.issue_datetime|date_format:"%Y/%m/%d %H:%M"}</td>
        <td>
{if $deceasedInfo.issue_state_code == "1" || $deceasedInfo.issue_state_code == "2"}
            －
{else}
            {$deceasedInfo.entry_datetime|date_format:"%Y/%m/%d"}
{/if}
        </td>
        <td>
{if $deceasedInfo.issue_state_code == "1"}
            発行依頼済み
{elseif $deceasedInfo.issue_state_code == "2"}
            発行中
{elseif $deceasedInfo.issue_state_code == "3"}
            発行完了
{elseif $deceasedInfo.issue_state_code == "4"}
            削除
{/if}
        </td>
        <td>
{if strlen($deceasedInfo.charge_name) > 0 }
            {$deceasedInfo.charge_name|escape}&nbsp;様
{else}
            －
{/if}
        </td>
{if $deceasedInfo.issue_state_code == "1"}
        <td class="operate">
            <a class="btn_mini" href="../mng/dispdeceasedinfo?did={$deceasedInfo.deceased_id}">表示</a>
        </td>
        <td class="operate">
            <a class="cancel_btn btn_mini" href="../mng/canceldeceasedqrorder?did={$deceasedInfo.deceased_id}" onclick="return false;" >キャンセル</a>
        </td>
{elseif $deceasedInfo.issue_state_code == "2"}
        <td class="operate">－</td>
        <td class="operate">－</td>
{elseif $deceasedInfo.issue_state_code == "3"}
        <td class="operate">
            <a class="btn_mini" href="../mng/dispdeceasedinfo?did={$deceasedInfo.deceased_id}">表示</a>
        </td>
        <td class="operate">
            <a class="btn_mini" href="../mng/downloadqrpdf?did={$deceasedInfo.deceased_id}">ダウンロード</a>
        </td>
{elseif $deceasedInfo.issue_state_code == "4"}
        <td class="operate">－</td>
        <td class="operate">－</td>
{/if}
        </td>
    </tr>
{/foreach}
</table>

<p class="page">{$now}/{$all}ページ　{$total}件中{$firstItemNumber}～{$lastItemNumber}件を表示</p>
<p class="page">
{foreach from = $pagesInRange item = "page"}
    {if $page == $now}
        {$page}&nbsp;&nbsp;
    {else}
        <a href="../mng/dispdeceasedpaging?page={$page}">{$page}</a>&nbsp;&nbsp;
    {/if}
{/foreach}
</p>

<!-- QR読込かつ通知可　総数 -->
        <h2 style="margin-top: 70px;">QR読込かつ通知可　総数</h2>
        {if empty($allinstallNumber) == false}
        <span>

            <table>
                 <tr>
                    <td colspan=3 align="center" style="font-size: 17px; background: #eee; width: 600px;">現在総数</td>
                </tr>
                <tr>
                    <td align="center" style="font-size: 17px; background: #eee;">Android</td>
                    <td align="center" style="font-size: 17px; background: #eee;">&nbsp;&nbsp;&nbsp;iOS&nbsp;&nbsp;&nbsp;</td>
                    <td align="center" style="font-size: 17px; background: #eee;">&nbsp;&nbsp;&nbsp;合計&nbsp;&nbsp;&nbsp;</td>
                </tr>

                <tr style="height: 200px;">
                    <td rowspan="3" align="center" style="font-size: 22px;">{$androidIdentificationNumberCount}</td>
                    <td rowspan="3" align="center" style="font-size: 22px;">{$iosIdentificationNumberCount}</td>
                    <td rowspan="3" align="center" style="font-size: 22px; font-weight: bold;">{$allIdentificationNumberCount}</td>
                </tr>
                <tr>

                </tr>
                <tr>

                </tr>
            </table>
            </span>
        {else}
                <p><span style="color:#ff0000;">インストール情報が登録されていません{$allIdentificationNumberCount}</span></p>
        {/if}