# 欢迎使用[reptileWxPhoto](https://github.com/Jibinghao/reptileWxPhoto)

主要方法说明如下：

- **getHtmlUrl()**：获取wx最新文章的链接，由于使用时间戳和签名导致了不能固定一个链接，这里建议每次都重新请求新的链接。（account_name_0代表搜索出来的第一个公众号，如果需要其他请更换数字）
- **getArticleUrl()**：文章里面会返回最近十篇wx文章链接，这里同时取出了wx号和wx公众号。
- **getImgUrl()**：wx这里用data-src才进行筛选，如果有其他字段更换data-src即可。

