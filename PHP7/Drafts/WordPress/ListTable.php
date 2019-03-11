<?php

declare(strict_types = 1);

namespace NSCL\WordPress;

if (!class_exists(__NAMESPACE__ . '\ListTable')) {

    abstract class ListTable
    {
        const SUPPORTS_AJAX = false;

        protected $singular = 'item';
        protected $plural   = 'items';

        /** @var array <i>[Slug => Title]</i> */
        protected $columns = [];

        /** @var string[] Sortable columns. The list of column slugs. */
        protected $sortable = [];

        /** @var string[] Hidden columns. The list of column slugs. */
        protected $hidden = [];

        /** @var array Items only for current page. */
        protected $items = [];

        protected $orderBy = 'date';
        protected $order   = 'DESC';

        public function __construct(string $singular, string $plural, bool $supportsAjax = false)
        {
            $this->singular     = $singular;
            $this->plural       = $plural;
            $this->supportsAjax = $supportsAjax;
        }

        /**
         * @return array <i>[Slug => Title]</i>
         */
        public abstract function getColumns(): array;

        /**
         * @return string[] The list of column slugs.
         */
        public abstract function getSortableColumns(): array;

        /**
         * @return string[] The list of column slugs.
         */
        public abstract function getHiddenColumns(): array;

        protected abstract function queryItems(int $offset, int $limit): array;

        protected abstract function getItemsTotalCount(): int;

        protected abstract function getDefaultItemsPerPageCount(): int;

        /**
         * Action links below the title (or another first column) of each item.
         *
         * @param mixed $item Single item.
         * @return array <i>[Slug => HTML &lt;a&gt; tag]</i>
         */
        public abstract function getRowActions($item): array;

        /**
         * @return array <i>[Slug => Title]</i>
         */
        public abstract function getBulkActions(): array;

        public abstract function processBulkAction(string $action, array $ids);

        /**
         * The method not so important when the ListTable have no bulk actions.
         *
         * @param mixed $item
         * @return mixed Item ID (number, string etc).
         */
        protected abstract function getItemId($item);

        /**
         * Sanitize item ID got from the request (sanitize_text_field(),
         * absint() etc).
         *
         * The method not so important when the ListTable have no bulk actions.
         *
         * @param mixed $itemId Item ID (number, string etc).
         * @return mixed Sanitized item ID.
         */
        protected abstract function sanitizeItemId($itemId);

        /**
         * This method will usually be used to query the database, sort and
         * filter the data, and generally get it ready to be displayed.
         */
        public function load()
        {
            $this->columns  = $this->getColumns();
            $this->sortable = $this->getSortableColumns();
            $this->hidden   = $this->getHiddenColumns();

            if (isset($_REQUEST['orderby'])) {
                $this->orderBy = sanitize_sql_orderby($_REQUEST['orderby']);

                // Remove order ("ASC", "DESC"), allowed by function
                // sanitize_sql_orderby()
                $this->orderBy = preg_replace('/\s+.*/', '', $this->orderBy);
            }

            if (isset($_REQUEST['order'])) {
                $order = strtoupper(sanitize_text_field($_REQUEST['order']));

                if (!in_array($order, ['ASC', 'DESC'])) {
                    $order = $this->order;
                }

                $this->order = $order;
            }

            $itemsPerPage = $this->getItemsPerPageCount();
            $itemsOffset  = $this->getItemsOffset();

            $this->items = $this->queryItems($itemsPerPage, $itemsOffset);

            $totalCount = $this->getItemsTotalCount();
            $pagesCount = ceil($totalCount / $itemsPerPage);

            $this->setupPagination($pagesCount, $totalCount, $itemsPerPage);
        }

        protected function processBulkActions()
        {
            $action = $this->getCurrentBulkAction();

            if (empty($action)) {
                return;
            }

            // Verify the nonce
            check_admin_referer('bulk-' . $this->getPlural());

            $ids = isset($_REQUEST['ids']) ? array_map([$this, 'sanitizeItemId'], $_REQUEST['ids']) : array();
            $ids = array_filter($ids);

            if (!empty($ids)) {
                $this->processBulkAction($action, $ids);
            }
        }

        /**
         * Required for bulk actions. The checkbox column is given a special
         * treatment when columns are processed.
         *
         * @param mixed $item Single item.
         * @return string
         */
        public function checkboxColumn($item): string
        {
            $itemId = $this->getItemId($item);
            return '<input type="checkbox" name="ids[]" value="' . esc_attr($itemId) . '" />';
        }

        /**
         * The method is called when the parent class can't find a method
         * specifically build for a given column.
         *
         * @param mixed $item Single item.
         * @param string $columnName
         * @return string
         */
        public function defaultColumn($item, string $columnName): string
        {
            // —
            return '<span aria-hidden="true">&#8212;</span>';
        }

        public function rowActions($item, bool $alwaysVisible = false)
        {
            $actions = $this->getRowActions($item);

            // todebug - Render actions
            return '';
        }

        public function getSingular(): string
        {
            return $this->singular;
        }

        public function getPlural(): string
        {
            return $this->plural;
        }

        protected function getItemsPerPageCount(): int
        {
            $defaultCount = $this->getDefaultItemsPerPageCount();

            $option = $this->plural . '_per_page';
            $userCount = (int)get_user_option($option);

            if (empty($userCount) || $userCount < 1) {
                $userCount = $defaultCount;
            }

            return $userCount;
        }

        protected function getItemsOffset(): int
        {
            $page = $this->getPageNumber();
            $itemsPerPage = $this->getItemsPerPageCount();

            return ($page - 1) * $itemsPerPage;
        }
    }

}
