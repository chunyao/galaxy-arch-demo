<?php

namespace App\Http\Controller\Helloword;

use App\Config\MQ;
use Galaxy\Common\Utils\SnowFlakeUtils;
use Mix\Vega\Context;


class SendMsg
{
    private $exchange = "ARCH_TEST2_EXCHANEG";
    private $routekey = "Qwer1234";

    public function __construct()
    {

    }

    public function handler(Context $ctx)
    {

        $id = rand(1, 1000000);
        $data['id']= GetUtilId('OtherId');
        $data['body'] = "眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。

眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。

眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。

眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。

眼下，今年以来最大范围高温正在影响我国。7月6日以来，四川盆地多地出现高温天气，甚至打破全年最高气温纪录，同时，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地出现了35℃以上的高温天气。

气象专家表示，此次高温天气范围广、强度强、持续时间长，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

四川热过吐鲁番，高温天气超长待机

连日来，四川高温天气持续，盆地内甚至出现了“高烧不退”。据统计，全国2418个国家级气象观测站7月6日17时的实时气温中，四川已经成功热过吐鲁番。


四川成都，孩童在一处喷泉戏水嬉闹。当日，成都迎来高温天气。图/中新图片网

这一天，全国最热的三个城市均在四川。其中，宜宾市高县最高温达40.5度，乐山市马边彝族自治县和成都简阳市最高温达40.3度。

炙烤模式让四川盆地热成了“红油锅底”，当地人直呼“简直熟透了”。网友拍摄的视频中，一只狗被地面烫得来回换脚；还有网友调侃，外面拌了一份凉菜，拿回家感觉吃上了冒菜。

国家应急广播网也显示，近期，四川发布数百条高温预警信号。至7月8日下午16时，当天共发布113条高温预警，其中有30条为高温红色预警。

据了解，高温预警信号分三级，分别以黄色、橙色、红色表示。高温黄色预警信号的标准是连续三天日最高气温将在35℃以上；高温橙色预警信号则是24小时内最高气温将升至37℃以上。而高温红色预警信号的标准是24小时内最高气温将升至40℃以上。

在气象学上，气温在35℃以上时可称为“高温天气”。如果连续几天最高气温都超过35℃时，即可称作“高温热浪”。高温热浪是指一段持续性的高温过程，由于高温持续时间较长，引起人、动物以及植物不能适应并且产生不利影响的一种气象灾害。

四川省气象台首席预报员吕学东向媒体表示，四川省本轮高温天气过程，主要与大陆高压长时间控制四川上空有关。“大气中盛行下沉气流，气块在下沉时，上面气压低、下面气压高，气块会被压缩，导致其内能增高、温度上升，再加上晴空辐射作用，共同推动气温升高。”

中国天气网则指出，北非-伊朗高压会向东发展和大陆高压连成一片，在我国西部高原形成一个异常强的暖高压，7月6-8日，给西北地区、四川盆地等地带来凶猛的高温。未来，北非-伊朗高压和大陆高压，甚至有可能再和副热带高压连成一体，导致大范围的高温出现。

除了四川，7月6日以来，新疆、西北地区东部、西南地区东部、华北、黄淮、浙江等地也出现了35℃以上的高温天气。

中央气象台首席预报员、正研级高级工程师符娇兰表示，此次高温天气过程是今年以来影响范围最广的一次高温天气，具有影响范围广、强度强、持续时间长等特点，西北地区东部、西南地区东部等地的部分地区日最高气温将接近或突破历史同期极值，部分地区高温天气将持续10天或以上。

符娇兰说，受副热带高压西伸北抬影响，未来高温天气影响范围进一步扩大，南方大部地区将出现持续性高温天气。

高温天气会是常态吗？

实际上，自今年6月中旬以来，我国就已出现了大范围高温天气过程。

7月8日，国新办举行的防汛救灾工作国务院政策例行吹风会上，中国气象局应急减灾与公共服务司负责人王亚伟表示，6月我国平均气温21.3℃，较常年同期偏高了0.9℃，为1961年以来历史同期最高。其中，河南、陕西、甘肃、宁夏、山西、山东、江苏、安徽6月气温均为历史同期最高。

6月13日以来，区域性高温天气过程一直在维持，高温强度强、持续时间长、影响范围广、影响人口多。6月25日，河北省、河南省分别有90个和29个国家气象站达到或超过40℃的气温，其中河北的灵寿县达到了44.2℃，为今年出现的最高气温。


7月8日，重庆街头，市民顶着炎炎烈日出行。图/中新图片网

对于我国6月中旬以来的持续高温天气，国家气象中心副主任方翔表示，中国北方大部地区处于强大的暖性高压系统控制，盛行下沉气流，一方面造成下沉增温，一方面有利于出现晴空辐射增温，加之大气干燥，白天地面受太阳辐射影响，升温迅速。此环流形势稳定维持，导致北方地区出现持续性高温天气。此外，偏西气流在太行山东麓下沉增温，对河北、河南等地的极端高温天气也有重要作用。

符娇兰则认为，夏季出现大范围高温天气属正常现象，但是今年以来出现的高温天气在强度和持续时间上存在一定的极端性，如四川、甘肃等地气温相继突破历史极值。

今年高温天气是否提前？国家气候中心汛期值班首席、研究员袁媛此前表示，时间并没有偏早，反而是稍晚点。以北京为例，与往年相比，今年五六月份冷空气比较活跃，整体感觉凉爽。

方翔还提到，此次高温天气过程覆盖面积广、持续时间长且具有极端性，高温少雨导致豫鲁苏皖甘陕等地旱情持续发展，北方部分地区电网用电负荷创新高，持续性高温也对新能源供应造成影响。

由于此次高温强度强且持续时间长，对电力供应、生产生活用水、车辆出行、农业生产均造成不利影响。符娇兰建议，各相关部门需关注天气预报预警信息，做好电力供应和水资源调度，并防范城市和林区火灾。特别是长江中下游高温持续时间长，需加强防御工作。

值得关注的是，放眼全球，西班牙、意大利、挪威、日本、伊朗和芬兰等国家，近期也出现高温天气。袁媛表示，印度和美国的高温天气与副热带高压系统的偏强密切相关，而欧洲和我国的高温主要与中纬度高压脊的加强有关。

不久前，世界气象组织（WMO）发布的《2021年全球气候状况》提到，过去一年，温室气体浓度、海平面上升、海洋热量和海洋酸化四个关键气候变化指标都创下新纪录。这是人类活动造成全球范围内陆地、海洋和大气变化的又一明显迹象，将对可持续发展和生态系统有持久危害。

上述报告还提到，2015—2021年是有记录以来最热的7年。2021年，大气中温室气体浓度继续上升，虽然年初和年底的拉尼娜事件带来暂时的降温效果，但没有扭转气温上升的总体趋势，当年全球平均气温比工业化前水平高出了约1.11摄氏度。

今年5月，英国伦敦帝国理工学院格兰瑟姆气候变化与环境研究所的科学家弗里德里克·奥托在一份报告中表示，在人为造成的气候变化开始之前，像今年南亚地区出现极端高温天气的概率大约为每3000年一次。

奥托和世界气候归因组织的研究人员发现，迄今为止，全球变暖1.2℃的现实，已经将与南亚地区这样持续时间和强度相似的极端高温的所谓回归期缩短至百年一遇。但随着地球继续升温，这种致命热浪之间的发生间隔将进一步缩小。研究人员预计，如果地球的平均地表温度比工业化前水平再上升0.8℃，预计像这样的热浪每5年就会发生一次。" . $id;
        MQ::instance()->publish(json_encode($data), $this->exchange,$this->routekey);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);

        return "200";
    }

    public function __destruct()
    {

    }

}