package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

import it.gmariotti.cardslib.library.internal.Card;
import it.gmariotti.cardslib.library.internal.CardArrayAdapter;
import it.gmariotti.cardslib.library.internal.CardExpand;
import it.gmariotti.cardslib.library.internal.CardHeader;
import it.gmariotti.cardslib.library.internal.CardThumbnail;
import it.gmariotti.cardslib.library.view.CardListView;

public class NewsActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_news);
        showNews();
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }

    public void chooseSocialManager(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    public void chooseNews(View view) {
        Intent newsIntent = new Intent(this, NewsActivity.class);
        startActivity(newsIntent);
        finish();
    }

    public void chooseMarketInsight(View view) {
        Intent marketIntent = new Intent(this, MarketInsightActivity.class);
        startActivity(marketIntent);
        finish();
    }

    public void chooseEmployeeSearch(View view) {

    }

    private void showNews() {
        String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.newsUrl);
        Client client = new Client(url, null, "GET");
        client.execute();

        JSONObject response;
        ArrayList<Card> cards = new ArrayList<Card>();
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);

            JSONArray newsJs = (JSONArray) response.get("news");
            for (int i = 0; i < newsJs.length(); i++) {
                JSONObject news = (JSONObject) newsJs.get(i);
                String imageUrl = news.getString("image_url");
                String time = news.getString("time");
                String title = news.getString("title");
                String header = news.getString("header");
                String readMore = news.getString("read_more");

                // Card
                Card card = new Card(this);
                CardHeader headerCard = new CardHeader(this);
                headerCard.setTitle(title);
                card.setTitle(header);
                card.addCardHeader(headerCard);

                // Thumbnail
                CardThumbnail thumbnailCard = new CardThumbnail(this);
                thumbnailCard.setUrlResource(imageUrl);
                card.addCardThumbnail(thumbnailCard);

                // Expand
                CardExpand expandCard = new CardExpand(this);
                expandCard.setTitle(time);
                card.addCardExpand(expandCard);
                cards.add(card);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        CardArrayAdapter mCardArrayAdapter = new CardArrayAdapter(this, cards);
        CardListView listView = (CardListView) this.findViewById(R.id.carddemo);
        if (listView != null) {
            listView.setAdapter(mCardArrayAdapter);
        }
    }
}
