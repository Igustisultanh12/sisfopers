package id.my.sisfoperskc.app.ui.components

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyListScope
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.derivedStateOf
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.runtime.snapshotFlow
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.path
import id.my.sisfoperskc.app.data.toPage
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.distinctUntilChanged
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

/** Pemuat halaman tunggal (props halaman Inertia). */
class PageLoader(private val scope: CoroutineScope, private val fetch: suspend () -> ApiResult) {
    var data by mutableStateOf<JsonObject?>(null)
    var error by mutableStateOf<String?>(null)
    var loading by mutableStateOf(false)
    var refreshing by mutableStateOf(false)
    private var job: Job? = null

    fun load(silent: Boolean = false) {
        job?.cancel()
        job = scope.launch {
            if (silent) refreshing = true else if (data == null) loading = true
            when (val r = fetch()) {
                is ApiResult.Ok -> { data = r.data; error = null }
                is ApiResult.Err -> if (data == null || !silent) error = r.message else Ui.error(r.message)
            }
            loading = false
            refreshing = false
        }
    }

    fun refresh() = load(silent = true)
}

@Composable
fun rememberPage(path: String, query: Map<String, Any?> = emptyMap(), key: Any? = null): PageLoader {
    val scope = rememberCoroutineScope()
    val loader = remember(path, query, key) { PageLoader(scope) { Api.get(path, query) } }
    LaunchedEffect(loader) { loader.load() }
    return loader
}

@Composable
fun rememberLoader(key: Any? = null, fetch: suspend () -> ApiResult): PageLoader {
    val scope = rememberCoroutineScope()
    val loader = remember(key) { PageLoader(scope, fetch) }
    LaunchedEffect(loader) { loader.load() }
    return loader
}

/** Kerangka konten halaman: memuat → galat → konten, dengan tarik-untuk-segarkan. */
@Composable
fun PageContent(
    loader: PageLoader,
    modifier: Modifier = Modifier,
    scrollable: Boolean = true,
    padding: PaddingValues = PaddingValues(16.dp),
    skeleton: @Composable () -> Unit = { SkeletonList() },
    content: @Composable ColumnScope.(JsonObject) -> Unit,
) {
    val data = loader.data
    PullToRefreshBox(isRefreshing = loader.refreshing, onRefresh = { loader.refresh() }, modifier = modifier.fillMaxSize()) {
        AnimatedContent(
            targetState = when {
                data != null -> 2
                loader.error != null -> 1
                else -> 0
            },
            transitionSpec = { fadeIn() togetherWith fadeOut() },
            label = "page",
        ) { state ->
            when (state) {
                0 -> Box(Modifier.fillMaxSize()) { skeleton() }
                1 -> Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
                    ErrorState(loader.error ?: "", onRetry = { loader.load() })
                }
                else -> {
                    val d = loader.data ?: return@AnimatedContent
                    Column(
                        Modifier
                            .fillMaxSize()
                            .then(if (scrollable) Modifier.verticalScroll(rememberScrollState()) else Modifier)
                            .padding(padding),
                        verticalArrangement = Arrangement.spacedBy(14.dp),
                    ) {
                        content(d)
                        Spacer(Modifier.height(90.dp))
                    }
                }
            }
        }
    }
}

/** Status daftar berhalaman (paginator Laravel) dengan pencarian & filter. */
class PagedState(
    private val scope: CoroutineScope,
    val path: String,
    val listKey: String,
    private val pageParam: String = "page",
    private val searchParam: String = "search",
) {
    val items = mutableStateListOf<JsonObject>()
    var root by mutableStateOf<JsonObject?>(null)
    var page by mutableIntStateOf(0)
    var lastPage by mutableIntStateOf(1)
    var total by mutableIntStateOf(0)
    var loading by mutableStateOf(false)
    var refreshing by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)
    var search by mutableStateOf("")
    val filters = androidx.compose.runtime.mutableStateMapOf<String, String>()
    private var job: Job? = null

    val hasMore get() = page < lastPage

    private fun query(p: Int): Map<String, Any?> {
        val q = mutableMapOf<String, Any?>(pageParam to p)
        if (search.isNotBlank()) q[searchParam] = search.trim()
        filters.forEach { (k, v) -> if (v.isNotBlank()) q[k] = v }
        return q
    }

    fun reload(silent: Boolean = false) {
        job?.cancel()
        job = scope.launch {
            if (silent) refreshing = true else loading = true
            error = null
            when (val r = Api.get(path, query(1))) {
                is ApiResult.Ok -> {
                    root = r.data
                    val pg = r.data.path(listKey).toPage()
                    items.clear(); items.addAll(pg.items)
                    page = pg.currentPage; lastPage = pg.lastPage; total = pg.total
                }
                is ApiResult.Err -> error = r.message
            }
            loading = false; refreshing = false
        }
    }

    fun loadMore() {
        if (loading || !hasMore || job?.isActive == true) return
        job = scope.launch {
            loading = true
            when (val r = Api.get(path, query(page + 1))) {
                is ApiResult.Ok -> {
                    val pg = r.data.path(listKey).toPage()
                    val existing = items.map { it["id"]?.toString() + it["uuid"]?.toString() }.toSet()
                    items.addAll(pg.items.filter { (it["id"]?.toString() + it["uuid"]?.toString()) !in existing })
                    page = pg.currentPage; lastPage = pg.lastPage; total = pg.total
                }
                is ApiResult.Err -> Ui.error(r.message)
            }
            loading = false
        }
    }

    fun replace(predicate: (JsonObject) -> Boolean, newItem: JsonObject?) {
        val i = items.indexOfFirst(predicate)
        if (i >= 0) {
            if (newItem == null) items.removeAt(i) else items[i] = newItem
        }
    }
}

@Composable
fun rememberPaged(
    path: String,
    listKey: String,
    initialFilters: Map<String, String> = emptyMap(),
    pageParam: String = "page",
    searchParam: String = "search",
): PagedState {
    val scope = rememberCoroutineScope()
    val state = remember(path, listKey) {
        PagedState(scope, path, listKey, pageParam, searchParam).apply { filters.putAll(initialFilters) }
    }
    LaunchedEffect(state) { state.reload() }
    return state
}

/**
 * Daftar berhalaman lengkap: pencarian, chip filter, header, item beranimasi, infinite scroll.
 */
@Composable
fun PagedList(
    state: PagedState,
    modifier: Modifier = Modifier,
    searchHint: String? = "Cari...",
    filterBar: (@Composable () -> Unit)? = null,
    header: (LazyListScope.(JsonObject?) -> Unit)? = null,
    emptyTitle: String = "Belum ada data",
    emptyMessage: String = "Data akan tampil di sini setelah tersedia.",
    contentPadding: PaddingValues = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 110.dp),
    itemSpacing: androidx.compose.ui.unit.Dp = 12.dp,
    item: @Composable (JsonObject, Int) -> Unit,
) {
    val listState = rememberLazyListState()
    val reachedEnd by remember {
        derivedStateOf {
            val last = listState.layoutInfo.visibleItemsInfo.lastOrNull()?.index ?: 0
            last >= listState.layoutInfo.totalItemsCount - 3
        }
    }
    LaunchedEffect(listState) {
        snapshotFlow { reachedEnd }.distinctUntilChanged().collect { if (it) state.loadMore() }
    }
    // Debounce pencarian
    var firstSearch by remember { mutableStateOf(true) }
    LaunchedEffect(state.search) {
        if (firstSearch) { firstSearch = false; return@LaunchedEffect }
        delay(450)
        state.reload()
    }

    PullToRefreshBox(isRefreshing = state.refreshing, onRefresh = { state.reload(silent = true) }, modifier = modifier.fillMaxSize()) {
        LazyColumn(
            state = listState,
            contentPadding = contentPadding,
            verticalArrangement = Arrangement.spacedBy(itemSpacing),
            modifier = Modifier.fillMaxSize(),
        ) {
            header?.invoke(this, state.root)
            if (searchHint != null) {
                item(key = "__search") {
                    PlainTextField(
                        state.search, { state.search = it },
                        placeholder = searchHint, icon = Icons.Outlined.Search, imeAction = ImeAction.Search,
                        onDone = { state.reload() },
                    )
                }
            }
            if (filterBar != null) item(key = "__filters") { filterBar() }
            if (state.items.isNotEmpty() || state.total > 0) {
                item(key = "__count") {
                    Text("${state.total} data", style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(start = 4.dp))
                }
            }
            when {
                state.items.isEmpty() && state.loading -> item(key = "__skeleton") { SkeletonList(4, Modifier.padding(horizontal = 0.dp)) }
                state.items.isEmpty() && state.error != null -> item(key = "__error") { ErrorState(state.error ?: "", { state.reload() }) }
                state.items.isEmpty() && state.root != null -> item(key = "__empty") { EmptyState(emptyTitle, emptyMessage) }
            }
            itemsIndexed(state.items, key = { i, it -> (it["uuid"] ?: it["id"])?.toString() ?: "i$i" }) { i, it ->
                Box(Modifier.appear(i % 10, key = it["uuid"] ?: it["id"] ?: i)) { item(it, i) }
            }
            if (state.items.isNotEmpty() && state.loading) {
                item(key = "__more") {
                    Box(Modifier.fillMaxWidth().padding(16.dp), contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(Modifier.size(26.dp), color = Sx.Blue, strokeWidth = 2.5.dp)
                    }
                }
            }
        }
    }
}

@Composable
fun FilterRow(options: List<Opt>, selected: String, onSelect: (String) -> Unit) {
    LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp), contentPadding = PaddingValues(vertical = 2.dp)) {
        itemsIndexed(options) { _, o -> Pill(o.label, o.value == selected, { onSelect(o.value) }) }
    }
}

@Composable
fun PagedState.filterChips(key: String, options: List<Opt>) {
    FilterRow(options, filters[key] ?: "") { v ->
        filters[key] = v
        reload()
    }
}

@Composable
fun StatRow(content: @Composable androidx.compose.foundation.layout.RowScope.() -> Unit) {
    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp), content = content)
}
